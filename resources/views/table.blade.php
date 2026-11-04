<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
<div x-data="{
 raw: $wire.$entangle(@js($getStatePath())), state: null, messages: @js(__('filament-table-field::table.messages')),
 init() { this.state=typeof this.raw==='string'?JSON.parse(this.raw):this.raw; this.$watch('state',value=>{this.raw=JSON.stringify(value)}); this.$watch('raw',value=>{const next=typeof value==='string'?JSON.parse(value):value; if(JSON.stringify(next)!==JSON.stringify(this.state)){this.state=next;this.selected=null;} }); },
 selected: null, endRow: 1, endColumn: 1, message: '',
 options: @js($field->getTableOptions()),
 text() { return {nodeType:'text',value:'',marks:[],data:[]}; },
 paragraph() { return {nodeType:'paragraph',data:[],content:[this.text()]}; },
 cell() { return {rowspan:1,colspan:1,hidden:false,content:{nodeType:'document',data:[],content:[this.paragraph()]},cellStyle:{backgroundColor:null,textAlign:null}}; },
 start() { this.state={version:1,rows:this.options.minRows ?? 1,columns:this.options.minColumns ?? 1,cells:[]}; for(let r=0;r<this.state.rows;r++) this.state.cells.push(Array.from({length:this.state.columns},()=>this.cell())); },
 active() { return this.selected ? this.state?.cells?.[this.selected[0]]?.[this.selected[1]] : null; },
 label(cell) { return (cell.content?.content ?? []).map(p=>(p.content ?? []).map(n=>n.nodeType==='hyperlink'?(n.content ?? []).map(t=>t.value).join(''):n.value).join('')).join('\n'); },
 pick(r,c) { this.selected=[r,c]; this.endRow=r+1;this.endColumn=c+1;this.message=''; },
 mark(node,type,value=null) { let i=node.marks.findIndex(m=>m.type===type); if(i>=0) node.marks.splice(i,1); else node.marks.push(value===null?{type}:{type,attrs:{value}}); },
 add(axis) { if(!this.state) return this.start(); if(axis==='row'){ if(this.state.rows>=Math.min(this.options.maxRows ?? 1000,1000)) return; this.state.cells.push(Array.from({length:this.state.columns},()=>this.cell()));this.state.rows++; }else{if(this.state.columns>=Math.min(this.options.maxColumns ?? 200,200))return;this.state.cells.forEach(row=>row.push(this.cell()));this.state.columns++;} },
 remove(axis) { if(!this.state) return; const row=axis==='row'; if((row?this.state.rows:this.state.columns)<=(row?(this.options.minRows ?? 1):(this.options.minColumns ?? 1)))return;
 for(let r=0;r<this.state.rows;r++)for(let c=0;c<this.state.columns;c++){let cell=this.state.cells[r][c];if(cell.hidden || cell.rowspan>1 || cell.colspan>1){this.message=this.messages.split_before_delete;return;}}
 if(row){this.state.cells.pop();this.state.rows--;}else{this.state.cells.forEach(cells=>cells.pop());this.state.columns--;}this.selected=null; },
 merge() { if(!this.selected)return;const [r,c]=this.selected,er=Number(this.endRow)-1,ec=Number(this.endColumn)-1;
 if(er<r||ec<c||er>=this.state.rows||ec>=this.state.columns){this.message=this.messages.merge_range_invalid;return;}
 for(let i=r;i<=er;i++)for(let j=c;j<=ec;j++){let cell=this.state.cells[i][j];if(cell.hidden||cell.rowspan>1||cell.colspan>1){this.message=this.messages.split_before_merge;return;}}
 const anchor=this.active();for(let i=r;i<=er;i++)for(let j=c;j<=ec;j++){if(i===r&&j===c)continue;let cell=this.state.cells[i][j];anchor.content.content.push(...cell.content.content);cell.hidden=true;cell.anchor=[r,c];}
 anchor.rowspan=er-r+1;anchor.colspan=ec-c+1; },
 split() { const cell=this.active();if(!cell)return;let[r,c]=this.selected;for(let i=r;i<r+cell.rowspan;i++)for(let j=c;j<c+cell.colspan;j++){let item=this.state.cells[i][j];item.hidden=false;delete item.anchor;}cell.rowspan=1;cell.colspan=1; }
}" style="display:grid;gap:12px">
    <div style="display:flex;gap:12px;flex-wrap:wrap"><button type="button" @click="add('row')">{{ __('filament-table-field::table.add_row') }}</button><button type="button" @click="add('column')">{{ __('filament-table-field::table.add_column') }}</button><button type="button" @click="remove('row')">{{ __('filament-table-field::table.remove_last_row') }}</button><button type="button" @click="remove('column')">{{ __('filament-table-field::table.remove_last_column') }}</button></div>
    <div style="overflow:auto"><table style="border-collapse:collapse;width:100%"><tbody>
        <template x-for="(row,r) in state?.cells ?? []" :key="r"><tr>
            <template x-for="(cell,c) in row" :key="c"><template x-if="!cell.hidden"><td :rowspan="cell.rowspan" :colspan="cell.colspan" :style="{backgroundColor:cell.cellStyle?.backgroundColor,textAlign:cell.cellStyle?.textAlign}" style="border:1px solid #aaa;padding:8px;min-width:100px"><button type="button" @click="pick(r,c)" :aria-label="@js(__('filament-table-field::table.edit_cell'))+' '+(r+1)+'-'+(c+1)" x-text="label(cell)||@js(__('filament-table-field::table.empty_cell'))" style="white-space:pre-wrap;width:100%"></button></td></template></template>
        </tr></template>
    </tbody></table></div>
    <p role="alert" x-text="message"></p>
    <template x-if="active()"><div style="border:1px solid #aaa;padding:12px;display:grid;gap:10px">
        <strong x-text="@js(__('filament-table-field::table.cell'))+' '+(selected[0]+1)+'-'+(selected[1]+1)"></strong>
        <div><label>{{ __('filament-table-field::table.merge_end_row') }} <input type="number" min="1" :max="state.rows" aria-label="{{ __('filament-table-field::table.merge_end_row') }}" x-model="endRow" style="width:70px" /></label><label>{{ __('filament-table-field::table.merge_end_column') }} <input type="number" min="1" :max="state.columns" aria-label="{{ __('filament-table-field::table.merge_end_column') }}" x-model="endColumn" style="width:70px" /></label><button type="button" @click="merge()">{{ __('filament-table-field::table.merge') }}</button><button type="button" @click="split()">{{ __('filament-table-field::table.split') }}</button></div>
        <div><label>{{ __('filament-table-field::table.cell_background') }} <input type="color" :value="active().cellStyle.backgroundColor ?? '#ffffff'" @input="active().cellStyle.backgroundColor=$event.target.value" /></label><label>{{ __('filament-table-field::table.alignment') }} <select aria-label="{{ __('filament-table-field::table.cell_alignment') }}" :value="active().cellStyle.textAlign ?? ''" @input="active().cellStyle.textAlign=$event.target.value || null"><option value="">{{ __('filament-table-field::table.default') }}</option><option value="left">{{ __('filament-table-field::table.left') }}</option><option value="center">{{ __('filament-table-field::table.center') }}</option><option value="right">{{ __('filament-table-field::table.right') }}</option></select></label></div>
        <template x-for="(paragraph,p) in active().content.content" :key="p"><div style="display:grid;gap:8px">
            <template x-for="(node,n) in paragraph.content" :key="n"><div style="border:1px solid #ddd;padding:8px">
                <template x-if="node.nodeType==='text'"><div><input type="text" aria-label="{{ __('filament-table-field::table.cell_text') }}" x-model="node.value" /><button type="button" @click="mark(node,'bold')" :aria-pressed="node.marks.some(m=>m.type==='bold')">{{ __('filament-table-field::table.bold') }}</button><button type="button" @click="mark(node,'italic')" :aria-pressed="node.marks.some(m=>m.type==='italic')">{{ __('filament-table-field::table.italic') }}</button><button type="button" @click="mark(node,'underline')" :aria-pressed="node.marks.some(m=>m.type==='underline')">{{ __('filament-table-field::table.underline') }}</button>
                    <label>{{ __('filament-table-field::table.text_color') }}<input type="color" @input="node.marks=node.marks.filter(m=>m.type!=='textColor');node.marks.push({type:'textColor',attrs:{value:$event.target.value}})" /></label>
                    <label>{{ __('filament-table-field::table.font_size') }}<input type="number" min="10" max="48" :value="node.marks.find(m=>m.type==='fontSize')?.attrs?.value ?? 16" @input="node.marks=node.marks.filter(m=>m.type!=='fontSize');node.marks.push({type:'fontSize',attrs:{value:Number($event.target.value)}})" /></label>
                </div></template>
                <template x-if="node.nodeType==='hyperlink'"><div><label>{{ __('filament-table-field::table.link') }}<input type="text" aria-label="{{ __('filament-table-field::table.link') }}" x-model="node.data.uri" /></label><select aria-label="{{ __('filament-table-field::table.link_target') }}" x-model="node.data.target"><option value="_self">{{ __('filament-table-field::table.current_window') }}</option><option value="_blank">{{ __('filament-table-field::table.new_window') }}</option></select><template x-for="(text,t) in node.content" :key="t"><input type="text" aria-label="{{ __('filament-table-field::table.link_text') }}" x-model="text.value" /></template></div></template>
                <button type="button" @click="paragraph.content.splice(n,1)">{{ __('filament-table-field::table.delete_fragment') }}</button>
            </div></template>
            <div><button type="button" @click="paragraph.content.push(text())">{{ __('filament-table-field::table.add_text') }}</button><button type="button" @click="paragraph.content.push({nodeType:'hyperlink',data:{uri:'',target:'_self'},content:[text()]})">{{ __('filament-table-field::table.add_link') }}</button><button type="button" @click="active().content.content.splice(p,1)">{{ __('filament-table-field::table.delete_paragraph') }}</button></div>
        </div></template>
        <button type="button" @click="active().content.content.push(paragraph())">{{ __('filament-table-field::table.add_paragraph') }}</button>
    </div></template>
</div>
</x-dynamic-component>
