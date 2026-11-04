<?php

return [
    'invalid_payload' => 'Table data must be an array.',
    'validation' => [
        'max_rows' => 'The table may contain no more than :max rows.', 'max_columns' => 'The table may contain no more than :max columns.', 'max_cells' => 'The table exceeds the maximum allowed cell count.',
        'minimum_dimensions' => 'The table must contain at least one row and one column.', 'row_count' => 'The table rows do not match the declared row count.', 'column_count' => 'The table columns do not match the declared column count.',
        'cell_object' => 'Each table cell must be an object.', 'merge_bounds' => 'A merged cell extends beyond the table bounds.', 'merge_overlap' => 'Merged cells may not overlap.', 'visible_region' => 'A visible cell is outside a valid region.', 'covered_hidden' => 'A cell covered by a merge must be hidden.',
        'anchor_required' => 'A hidden cell must reference a valid merge anchor.', 'anchor_bounds' => 'A merge anchor must be inside the table.', 'anchor_mismatch' => 'The hidden cell anchor does not match a merged region.',
        'content_document' => 'Visible cells must contain a valid content document.', 'document_root' => 'The content root must be a document node.', 'document_content' => 'Document nodes must contain a content array.',
        'paragraph_nodes' => 'Document nodes may only contain paragraphs.', 'paragraph_content' => 'Paragraph nodes must contain a content array.', 'paragraph_children' => 'Paragraphs may only contain text or link nodes.',
        'link_uri' => 'A link must contain a non-empty URL.', 'link_target' => 'A link target is invalid.', 'link_content' => 'A link must contain text.', 'link_children' => 'A link may only contain text nodes.',
        'text_value' => 'Text nodes must contain a string value.', 'text_marks' => 'Text marks must be an array.', 'mark_object' => 'Text marks must be objects.', 'mark_type' => 'A text mark type is invalid.', 'font_size' => 'Font size must be an integer between 10 and 48.',
        'cell_style' => 'Visible cells must contain valid cell styles.', 'alignment' => 'Cell text alignment is invalid.', 'color_string' => 'Color values must be valid CSS color strings.', 'color_format' => 'Colors must use hex, rgb(), or rgba() format.',
    ],
    'messages' => ['split_before_delete' => 'Split merged cells before deleting rows or columns.', 'merge_range_invalid' => 'The merge range is invalid.', 'split_before_merge' => 'Split selected cells before merging.'],
    'add_row' => 'Add row', 'add_column' => 'Add column', 'remove_last_row' => 'Remove last row', 'remove_last_column' => 'Remove last column',
    'edit_cell' => 'Edit cell', 'empty_cell' => 'Empty cell', 'cell' => 'Cell',
    'merge_end_row' => 'Last row to merge', 'merge_end_column' => 'Last column to merge',
    'merge' => 'Merge cells', 'split' => 'Split cells', 'cell_background' => 'Cell background', 'alignment' => 'Alignment',
    'default' => 'Default', 'left' => 'Left', 'center' => 'Center', 'right' => 'Right', 'cell_alignment' => 'Cell alignment',
    'cell_text' => 'Cell text', 'bold' => 'Bold', 'italic' => 'Italic', 'underline' => 'Underline', 'text_color' => 'Text color', 'font_size' => 'Font size',
    'link' => 'Link', 'link_target' => 'Link target', 'current_window' => 'Current window', 'new_window' => 'New window', 'link_text' => 'Link text',
    'delete_fragment' => 'Delete text fragment', 'add_text' => 'Add text', 'add_link' => 'Add link', 'delete_paragraph' => 'Delete paragraph', 'add_paragraph' => 'Add paragraph',
];
