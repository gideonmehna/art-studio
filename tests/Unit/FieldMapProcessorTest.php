<?php

use PHPUnit\Framework\TestCase;

/**
 * Tests for the three pure PHP functions in inc/field-map-processor.php.
 *
 * Run from the plugin root:
 *   composer install
 *   vendor/bin/phpunit
 */
class FieldMapProcessorTest extends TestCase
{
    // ===================================================================
    // art_studio_build_post_from_field_map()
    // ===================================================================

    public function test_empty_field_map_returns_empty_buckets(): void
    {
        $result = art_studio_build_post_from_field_map([], ['text-1' => 'Hello']);

        $this->assertSame('', $result['post_title']);
        $this->assertSame('', $result['post_content']);
        $this->assertSame([], $result['meta_input']);
        $this->assertSame([], $result['taxonomy_terms']);
        $this->assertNull($result['upload_field']);
    }

    public function test_post_title_mapping(): void
    {
        $map    = ['text-1' => ['destination_type' => 'post_title', 'destination_key' => '']];
        $result = art_studio_build_post_from_field_map($map, ['text-1' => 'My Artwork']);

        $this->assertSame('My Artwork', $result['post_title']);
        $this->assertSame('My Artwork', $result['notify_title']);
    }

    public function test_post_title_missing_from_post_data_stays_empty(): void
    {
        $map    = ['text-1' => ['destination_type' => 'post_title', 'destination_key' => '']];
        $result = art_studio_build_post_from_field_map($map, []);

        $this->assertSame('', $result['post_title']);
    }

    public function test_post_content_mapping(): void
    {
        $map    = ['textarea-1' => ['destination_type' => 'post_content', 'destination_key' => '']];
        $result = art_studio_build_post_from_field_map($map, ['textarea-1' => 'A lovely painting.']);

        $this->assertSame('A lovely painting.', $result['post_content']);
    }

    public function test_meta_mapping_string_value(): void
    {
        $map    = ['name-1' => ['destination_type' => 'meta', 'destination_key' => '_artist_name']];
        $result = art_studio_build_post_from_field_map($map, ['name-1' => 'Alice']);

        $this->assertSame('Alice', $result['meta_input']['_artist_name']);
    }

    public function test_meta_mapping_artist_name_captured_for_notification(): void
    {
        $map    = ['name-1' => ['destination_type' => 'meta', 'destination_key' => '_artist_name']];
        $result = art_studio_build_post_from_field_map($map, ['name-1' => 'Alice']);

        $this->assertSame('Alice', $result['notify_name']);
    }

    public function test_meta_mapping_numeric_value_uses_absint(): void
    {
        $map    = ['number-1' => ['destination_type' => 'meta', 'destination_key' => '_artist_age']];
        $result = art_studio_build_post_from_field_map($map, ['number-1' => '8']);

        $this->assertSame(8, $result['meta_input']['_artist_age']);
    }

    public function test_meta_mapping_skipped_when_no_destination_key(): void
    {
        $map    = ['text-1' => ['destination_type' => 'meta', 'destination_key' => '']];
        $result = art_studio_build_post_from_field_map($map, ['text-1' => 'hello']);

        $this->assertSame([], $result['meta_input']);
    }

    public function test_name_concat_mapping_joins_first_and_last(): void
    {
        $map  = ['name-2' => ['destination_type' => 'name_concat', 'destination_key' => '_guardian_name']];
        $post = ['name-2-first-name' => 'John', 'name-2-last-name' => 'Doe'];

        $result = art_studio_build_post_from_field_map($map, $post);

        $this->assertSame('John Doe', $result['meta_input']['_guardian_name']);
    }

    public function test_name_concat_only_first_name_trims_trailing_space(): void
    {
        $map  = ['name-2' => ['destination_type' => 'name_concat', 'destination_key' => '_guardian_name']];
        $post = ['name-2-first-name' => 'John', 'name-2-last-name' => ''];

        $result = art_studio_build_post_from_field_map($map, $post);

        $this->assertSame('John', $result['meta_input']['_guardian_name']);
    }

    public function test_taxonomy_mapping_array_value(): void
    {
        $map  = ['checkbox-1' => ['destination_type' => 'taxonomy', 'destination_key' => 'art_emotion']];
        $post = ['checkbox-1' => ['happy', 'excited']];

        $result = art_studio_build_post_from_field_map($map, $post);

        $this->assertSame(['happy', 'excited'], $result['taxonomy_terms']['art_emotion']);
    }

    public function test_taxonomy_mapping_comma_separated_string(): void
    {
        $map  = ['text-3' => ['destination_type' => 'taxonomy', 'destination_key' => 'post_tag']];
        $post = ['text-3' => 'joyful, bright'];

        $result = art_studio_build_post_from_field_map($map, $post);

        $this->assertSame(['joyful', 'bright'], $result['taxonomy_terms']['post_tag']);
    }

    public function test_two_taxonomy_fields_same_taxonomy_are_merged(): void
    {
        $map = [
            'checkbox-1' => ['destination_type' => 'taxonomy', 'destination_key' => 'art_emotion'],
            'text-3'     => ['destination_type' => 'taxonomy', 'destination_key' => 'art_emotion'],
        ];
        $post = ['checkbox-1' => ['happy'], 'text-3' => 'excited'];

        $result = art_studio_build_post_from_field_map($map, $post);

        $this->assertSame(['happy', 'excited'], $result['taxonomy_terms']['art_emotion']);
    }

    public function test_featured_image_sets_upload_field(): void
    {
        $map    = ['upload-1' => ['destination_type' => 'featured_image', 'destination_key' => '']];
        $result = art_studio_build_post_from_field_map($map, []);

        $this->assertSame('upload-1', $result['upload_field']);
    }

    public function test_ignore_mapping_has_no_effect(): void
    {
        $map    = ['consent-1' => ['destination_type' => 'ignore', 'destination_key' => '']];
        $result = art_studio_build_post_from_field_map($map, ['consent-1' => 'yes']);

        $this->assertSame('', $result['post_title']);
        $this->assertSame([], $result['meta_input']);
    }

    public function test_unknown_destination_type_is_silently_ignored(): void
    {
        $map    = ['text-1' => ['destination_type' => 'evil_type', 'destination_key' => 'some_key']];
        $result = art_studio_build_post_from_field_map($map, ['text-1' => 'Hello']);

        $this->assertSame('', $result['post_title']);
        $this->assertSame([], $result['meta_input']);
    }

    public function test_html_tags_stripped_from_text_fields(): void
    {
        $map    = ['text-1' => ['destination_type' => 'post_title', 'destination_key' => '']];
        $result = art_studio_build_post_from_field_map($map, ['text-1' => '<b>Bold</b> title']);

        $this->assertSame('Bold title', $result['post_title']);
    }

    // ===================================================================
    // art_studio_parse_forminator_field()
    // ===================================================================

    public function test_parse_object_field_uses_slug_as_element_id(): void
    {
        $field              = new stdClass();
        $field->slug        = 'text-1';
        $field->type        = 'text';
        $field->field_label = 'Artwork Title';

        $result = art_studio_parse_forminator_field($field);

        $this->assertSame('text-1', $result['element_id']);
        $this->assertSame('text', $result['type']);
        $this->assertSame('Artwork Title', $result['label']);
    }

    public function test_parse_object_field_falls_back_to_slug_for_empty_label(): void
    {
        $field              = new stdClass();
        $field->slug        = 'upload-1';
        $field->type        = 'upload';
        $field->field_label = null;

        $result = art_studio_parse_forminator_field($field);

        $this->assertSame('upload-1', $result['label']);
    }

    public function test_parse_array_field_uses_id_key(): void
    {
        $field = ['id' => 'name-1', 'type' => 'name', 'field_label' => 'Artist Name'];

        $result = art_studio_parse_forminator_field($field);

        $this->assertSame('name-1', $result['element_id']);
        $this->assertSame('name', $result['type']);
    }

    public function test_parse_array_field_falls_back_to_element_id_key(): void
    {
        $field = ['element_id' => 'email-2', 'type' => 'email', 'field_label' => 'Guardian Email'];

        $result = art_studio_parse_forminator_field($field);

        $this->assertSame('email-2', $result['element_id']);
    }

    public function test_parse_field_with_no_id_returns_empty_array(): void
    {
        $field = ['id' => '', 'element_id' => '', 'type' => 'text', 'field_label' => 'Title'];

        $result = art_studio_parse_forminator_field($field);

        $this->assertSame([], $result);
    }

    // ===================================================================
    // art_studio_sanitize_field_map_entry()
    // ===================================================================

    public function test_sanitize_valid_post_title_entry_clears_destination_key(): void
    {
        $entry  = ['element_id' => 'text-1', 'destination_type' => 'post_title', 'destination_key' => 'should_be_cleared'];
        $result = art_studio_sanitize_field_map_entry($entry);

        $this->assertNotNull($result);
        $this->assertSame('post_title', $result['destination_type']);
        $this->assertSame('', $result['destination_key']); // key cleared for title/content/image
    }

    public function test_sanitize_valid_meta_entry(): void
    {
        $entry  = ['element_id' => 'text-1', 'destination_type' => 'meta', 'destination_key' => '_artist_name'];
        $result = art_studio_sanitize_field_map_entry($entry);

        $this->assertNotNull($result);
        $this->assertSame('meta', $result['destination_type']);
        $this->assertSame('_artist_name', $result['destination_key']);
    }

    public function test_sanitize_meta_without_key_returns_null(): void
    {
        $entry  = ['element_id' => 'text-1', 'destination_type' => 'meta', 'destination_key' => ''];
        $result = art_studio_sanitize_field_map_entry($entry);

        $this->assertNull($result);
    }

    public function test_sanitize_name_concat_without_key_returns_null(): void
    {
        $entry  = ['element_id' => 'name-2', 'destination_type' => 'name_concat', 'destination_key' => ''];
        $result = art_studio_sanitize_field_map_entry($entry);

        $this->assertNull($result);
    }

    public function test_sanitize_invalid_destination_type_defaults_to_ignore(): void
    {
        $entry  = ['element_id' => 'text-1', 'destination_type' => 'drop_table', 'destination_key' => ''];
        $result = art_studio_sanitize_field_map_entry($entry);

        $this->assertNotNull($result);
        $this->assertSame('ignore', $result['destination_type']);
    }

    public function test_sanitize_empty_element_id_returns_null(): void
    {
        $entry  = ['element_id' => '', 'destination_type' => 'post_title', 'destination_key' => ''];
        $result = art_studio_sanitize_field_map_entry($entry);

        $this->assertNull($result);
    }

    public function test_sanitize_taxonomy_entry_preserves_taxonomy_key(): void
    {
        $entry  = ['element_id' => 'checkbox-1', 'destination_type' => 'taxonomy', 'destination_key' => 'art_emotion'];
        $result = art_studio_sanitize_field_map_entry($entry);

        $this->assertNotNull($result);
        $this->assertSame('taxonomy', $result['destination_type']);
        $this->assertSame('art_emotion', $result['destination_key']);
    }

    public function test_sanitize_strips_html_from_destination_key(): void
    {
        $entry  = ['element_id' => 'text-1', 'destination_type' => 'meta', 'destination_key' => '<script>_key</script>'];
        $result = art_studio_sanitize_field_map_entry($entry);

        $this->assertNotNull($result);
        $this->assertSame('_key', $result['destination_key']);
    }
}
