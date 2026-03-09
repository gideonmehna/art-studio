<?php
/**
 * Pure PHP functions for processing Forminator field maps.
 *
 * These functions have no WordPress dependencies beyond the three
 * sanitization helpers (sanitize_text_field, sanitize_textarea_field, absint),
 * making them fully unit-testable without a WordPress environment.
 */

if (!defined('ABSPATH') && !defined('ART_STUDIO_TESTING')) {
    exit;
}

/**
 * Build post data buckets from a field map and submitted POST values.
 *
 * @param array $field_map  Stored field map for this form:
 *                          [ element_id => [ 'destination_type' => ..., 'destination_key' => ... ] ]
 * @param array $post_data  Submitted POST data (typically $_POST)
 * @return array {
 *     post_title     : string
 *     post_content   : string
 *     meta_input     : array  [ meta_key => mixed ]
 *     taxonomy_terms : array  [ taxonomy_slug => string[] ]
 *     upload_field   : string|null
 *     notify_title   : string
 *     notify_name    : string
 * }
 */
function art_studio_build_post_from_field_map(array $field_map, array $post_data): array
{
    $result = array(
        'post_title'     => '',
        'post_content'   => '',
        'meta_input'     => array(),
        'taxonomy_terms' => array(),
        'upload_field'   => null,
        'notify_title'   => '',
        'notify_name'    => '',
    );

    foreach ($field_map as $element_id => $mapping) {
        $destination_type = isset($mapping['destination_type']) ? $mapping['destination_type'] : 'ignore';
        $destination_key  = isset($mapping['destination_key'])  ? $mapping['destination_key']  : '';

        switch ($destination_type) {

            case 'post_title':
                $raw = sanitize_text_field(isset($post_data[$element_id]) ? $post_data[$element_id] : '');
                if (!empty($raw)) {
                    $result['post_title']   = $raw;
                    $result['notify_title'] = $raw;
                }
                break;

            case 'post_content':
                $result['post_content'] = sanitize_textarea_field(
                    isset($post_data[$element_id]) ? $post_data[$element_id] : ''
                );
                break;

            case 'meta':
                if (!empty($destination_key)) {
                    $raw = sanitize_text_field(isset($post_data[$element_id]) ? $post_data[$element_id] : '');
                    $result['meta_input'][$destination_key] = is_numeric($raw) ? absint($raw) : $raw;
                    if ($destination_key === '_artist_name') {
                        $result['notify_name'] = $raw;
                    }
                }
                break;

            case 'name_concat':
                // Forminator splits name fields into {id}-first-name / {id}-last-name in POST.
                if (!empty($destination_key)) {
                    $first = sanitize_text_field(
                        isset($post_data[$element_id . '-first-name']) ? $post_data[$element_id . '-first-name'] : ''
                    );
                    $last = sanitize_text_field(
                        isset($post_data[$element_id . '-last-name']) ? $post_data[$element_id . '-last-name'] : ''
                    );
                    $result['meta_input'][$destination_key] = trim($first . ' ' . $last);
                    if ($destination_key === '_artist_name') {
                        $result['notify_name'] = $result['meta_input'][$destination_key];
                    }
                }
                break;

            case 'taxonomy':
                if (!empty($destination_key)) {
                    $raw_value = isset($post_data[$element_id]) ? $post_data[$element_id] : null;
                    if (!is_null($raw_value)) {
                        // Checkboxes arrive as arrays; text fields as comma-separated strings.
                        if (is_array($raw_value)) {
                            $terms = array_map('sanitize_text_field', $raw_value);
                        } else {
                            $terms = array_map('trim', explode(',', sanitize_text_field($raw_value)));
                        }
                        $terms = array_values(array_filter($terms));
                        if (!empty($terms)) {
                            if (!isset($result['taxonomy_terms'][$destination_key])) {
                                $result['taxonomy_terms'][$destination_key] = array();
                            }
                            $result['taxonomy_terms'][$destination_key] = array_merge(
                                $result['taxonomy_terms'][$destination_key],
                                $terms
                            );
                        }
                    }
                }
                break;

            case 'featured_image':
                $result['upload_field'] = $element_id;
                break;

            case 'ignore':
            default:
                break;
        }
    }

    return $result;
}

/**
 * Extract element_id, type, and label from a Forminator field object or raw array.
 *
 * Forminator_Form_Field_Model uses PHP magic __get; isset() does NOT trigger __get
 * so we use $field->slug (a real public property) as the authoritative element_id.
 * For raw-meta arrays the stored key is 'id', not 'element_id'.
 *
 * Returns an empty array if no element_id can be resolved.
 *
 * @param object|array $field
 * @return array { element_id: string, type: string, label: string } | []
 */
function art_studio_parse_forminator_field($field): array
{
    if (is_object($field)) {
        $element_id = $field->slug;        // real public property — always reliable
        $type       = $field->type;        // via __get → $raw['type']
        $label      = $field->field_label; // via __get → $raw['field_label']
        if (empty($label)) {
            $label = $element_id;
        }
    } else {
        // Raw meta: key is 'id'; fall back to 'element_id' for older Forminator versions.
        $element_id = isset($field['id'])          ? $field['id']          : '';
        if (empty($element_id)) {
            $element_id = isset($field['element_id']) ? $field['element_id'] : '';
        }
        $type  = isset($field['type'])        ? $field['type']        : '';
        $label = isset($field['field_label']) ? $field['field_label'] : $element_id;
    }

    if (empty($element_id)) {
        return array();
    }

    return array(
        'element_id' => (string) $element_id,
        'type'       => (string) $type,
        'label'      => (string) $label,
    );
}

/**
 * Sanitize and validate a single field map entry received from the admin UI.
 *
 * Returns null if the entry is invalid and should be skipped entirely.
 * The element_id key is NOT included in the return value — the caller uses it as the map key.
 *
 * @param array $entry  Raw entry from JSON-decoded POST data.
 * @return array|null   [ 'destination_type' => ..., 'destination_key' => ... ] | null
 */
function art_studio_sanitize_field_map_entry(array $entry): ?array
{
    $valid_types      = array('post_title', 'post_content', 'meta', 'taxonomy', 'featured_image', 'ignore', 'name_concat');
    $no_key_types     = array('post_title', 'post_content', 'featured_image', 'ignore');
    $require_key_types = array('meta', 'name_concat');

    $element_id       = sanitize_text_field(isset($entry['element_id'])       ? $entry['element_id']       : '');
    $destination_type = sanitize_text_field(isset($entry['destination_type']) ? $entry['destination_type'] : 'ignore');
    $destination_key  = sanitize_text_field(isset($entry['destination_key'])  ? $entry['destination_key']  : '');

    if (empty($element_id)) {
        return null;
    }

    if (!in_array($destination_type, $valid_types, true)) {
        $destination_type = 'ignore';
    }

    if (in_array($destination_type, $no_key_types, true)) {
        $destination_key = '';
    }

    if (in_array($destination_type, $require_key_types, true) && empty($destination_key)) {
        return null; // meta / name_concat without a destination key is invalid
    }

    return array(
        'destination_type' => $destination_type,
        'destination_key'  => $destination_key,
    );
}
