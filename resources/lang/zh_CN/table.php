<?php

return [
    'invalid_payload' => '表格必须为数组。',
    'validation' => [
        'max_rows' => '表格最多可包含 :max 行。', 'max_columns' => '表格最多可包含 :max 列。', 'max_cells' => '表格单元格总数超过上限。',
        'minimum_dimensions' => '表格至少需要一行一列。', 'row_count' => '实际行数与声明的行数不符。', 'column_count' => '实际列数与声明的列数不符。',
        'cell_object' => '每个单元格都必须是对象。', 'merge_bounds' => '合并单元格超出表格范围。', 'merge_overlap' => '合并区域不能相互重叠。', 'visible_region' => '可见单元格不属于有效区域。', 'covered_hidden' => '被合并区域覆盖的单元格必须隐藏。',
        'anchor_required' => '隐藏单元格必须引用有效的合并锚点。', 'anchor_bounds' => '合并锚点必须位于表格范围内。', 'anchor_mismatch' => '隐藏单元格锚点与合并区域不符。',
        'content_document' => '可见单元格必须包含有效的内容文档。', 'document_root' => '内容根节点必须是 document。', 'document_content' => '文档节点必须包含内容数组。',
        'paragraph_nodes' => '文档节点只能包含段落。', 'paragraph_content' => '段落节点必须包含内容数组。', 'paragraph_children' => '段落只能包含文本或链接节点。',
        'link_uri' => '链接必须包含非空 URL。', 'link_target' => '链接打开方式无效。', 'link_content' => '链接必须包含文本。', 'link_children' => '链接节点只能包含文本节点。',
        'text_value' => '文本节点必须包含字符串值。', 'text_marks' => '文本格式标记必须为数组。', 'mark_object' => '文本格式标记必须为对象。', 'mark_type' => '文本格式类型无效。', 'font_size' => '字号必须是 10 到 48 之间的整数。',
        'cell_style' => '可见单元格必须包含有效样式。', 'alignment' => '单元格文字对齐方式无效。', 'color_string' => '颜色必须是有效的 CSS 颜色值。', 'color_format' => '颜色仅支持 hex、rgb() 或 rgba() 格式。',
    ],
    'messages' => ['split_before_delete' => '请先拆分合并单元格再删除行列。', 'merge_range_invalid' => '合并范围无效。', 'split_before_merge' => '请先拆分所选区域。'],
    'add_row' => '添加行', 'add_column' => '添加列', 'remove_last_row' => '删除末行', 'remove_last_column' => '删除末列',
    'edit_cell' => '编辑单元格', 'empty_cell' => '空单元格', 'cell' => '单元格',
    'merge_end_row' => '合并末行', 'merge_end_column' => '合并末列',
    'merge' => '合并单元格', 'split' => '拆分单元格', 'cell_background' => '单元格背景', 'alignment' => '对齐',
    'default' => '默认', 'left' => '左', 'center' => '中', 'right' => '右', 'cell_alignment' => '单元格对齐',
    'cell_text' => '单元格文字', 'bold' => '粗体', 'italic' => '斜体', 'underline' => '下划线', 'text_color' => '文字颜色', 'font_size' => '字号',
    'link' => '链接', 'link_target' => '链接目标', 'current_window' => '当前窗口', 'new_window' => '新窗口', 'link_text' => '链接文字',
    'delete_fragment' => '删除文字片段', 'add_text' => '添加文字', 'add_link' => '添加链接', 'delete_paragraph' => '删除段落', 'add_paragraph' => '添加段落',
];
