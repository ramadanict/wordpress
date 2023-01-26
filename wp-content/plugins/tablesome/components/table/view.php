<?php

namespace Tablesome\Components\Table;

if (!class_exists('\Tablesome\Components\Table\View')) {
    class View
    {
        public function get_table($viewProps)
        {
            $this->collection = $viewProps['collection'];
            $this->items = $viewProps['items'];

            $table_id = (isset($this->collection['post_id'])) ? $this->collection['post_id'] : $this->collection['table_id'];
            $table_mode = $this->collection['mode'];
            $this->tabIndex = $table_mode == 'editor' ? 'tabindex=0' : "";
            $hide_table_header = $this->collection['display']['hideTableHeader'] && $table_mode == 'read-only';

            $container_classes = $table_mode . " ";
            $container_classes .= !$this->collection['customstyle'] && $table_mode == 'read-only' ? "tablesome__customstyle " : "";
            $container_classes .= $this->get_display_classes($this->collection);

            $exclude_column_ids = isset($this->collection['exclude_column_ids']) ? $this->collection['exclude_column_ids'] : '';

            $html = '';

            /**
             *  ID selector "tablesome__container" added because of overriding the style of Divi builder plugin page only.
             *  Don't use it in the jQuery, javascript or svelte files.
             *  If you use the ID in any js related method it will cause a problem for multiple instances.
             *  Discussion Refer Link: https://github.com/essekia/tablesome/pull/536#discussion_r657837968
             */
            $html .= '<div id="tablesome__container" data-table-id="' . $table_id . '" class="tablesome__container tablesome__container--' . $table_id . '  tablesome__container--' . $container_classes . '" data-exclude-column-ids="' . $exclude_column_ids . '" data-page-limit="' . $this->collection['display']['numOfRecordsPerPage'] . '">';
            $html .= $this->get_skeleton();
            // $html .= $this->get_filter_button_and_search($table_mode);
            // $html .= '<div class="tablesome__table--wrapper">';
            // $html .= '<table id="' . $table_id . '" class="tablesome__table tablesome__table--nowrap tablesome__table--' . $table_mode . ' ">';
            // $html .= $this->get_table_head($viewProps, $hide_table_header);
            // $html .= $this->get_table_body($viewProps);
            // $html .= $this->get_table_foot($viewProps);

            // $html .= '</table>';
            // $html .= '</div>';
            $html .= '</div>';

            return $html;
        }

        protected function get_display_classes($collection)
        {
            $class = "";
            $isReadOnlyMode = $collection["mode"] == "read-only" ? true : false;
            $display = $collection["display"];

            $class .= $display['displayMode'] && $isReadOnlyMode ? $display['displayMode'] . " " : "";
            $class .= $display['hideTableHeader'] && $isReadOnlyMode ? "hideTableHeader " : "";
            $class .= $display['mobileLayoutMode'] && $isReadOnlyMode ? $display['mobileLayoutMode'] . " " : "scroll-mode ";
            $class .= $display['enableColumnMinWidth'] && $isReadOnlyMode ? "min-column-width " : "";
            $class .= $display['enableColumnMaxWidth'] && $isReadOnlyMode ? "max-column-width " : "";
            $class .= !$display['serialNumberColumn'] && $isReadOnlyMode ? "serialNumberColumn " : "";
            $class .= !$display['stickyFirstColumn'] && $isReadOnlyMode ? "stickyFirstColumn " : "";

            // Desktop

            $class .= !$display['desktop-search'] && $isReadOnlyMode ? "disable_sorting " : "";
            $class .= !$display['desktop-filter'] && $isReadOnlyMode ? "disable_filter " : "";
            $class .= !$display['desktop-search'] && $isReadOnlyMode ? "disable_search " : "";

            // Mobile
            $class .= !$display['mobile-sort'] && $isReadOnlyMode ? "sm-disable_sorting " : "";
            $class .= !$display['mobile-filter'] && $isReadOnlyMode ? "sm-disable_filter " : "";
            $class .= !$display['mobile-search'] && $isReadOnlyMode ? "sm-disable_search " : "";

            return $class;
        }

        public function get_skeleton()
        {

            $html = '<div class="tablesome-border tablesome-border-solid tablesome-border-slate-200 tablesome-rounded-sm tablesome-p-4  tablesome-w-full tablesome-mx-auto tablesome-my-6">
                <div class="tablesome-animate-pulse tablesome-space-y-4">
                    <div class="tablesome-flex tablesome-gap-4 tablesome-justify-end" >
                        <div class="tablesome-h-7 tablesome-w-40 tablesome-bg-slate-200 tablesome-rounded tablesome-col-span-1"></div>
                        <div class="tablesome-h-7 tablesome-w-20 tablesome-bg-slate-200 tablesome-rounded tablesome-col-span-2"></div>
                    </div>
                    <div class="tablesome-h-7 tablesome-bg-slate-200 tablesome-rounded tablesome-col-span-1"></div>

                    <div class="tablesome-grid tablesome-grid-cols-4 tablesome-gap-4">
                    <div class="tablesome-h-6 tablesome-bg-slate-200 tablesome-rounded tablesome-col-span-1"></div>
                    <div class="tablesome-h-6 tablesome-bg-slate-200 tablesome-rounded tablesome-col-span-1"></div>
                    <div class="tablesome-h-6 tablesome-bg-slate-200 tablesome-rounded tablesome-col-span-1"></div>
                    <div class="tablesome-h-6 tablesome-bg-slate-200 tablesome-rounded tablesome-col-span-1"></div>
                    </div>

                    <div class="tablesome-grid tablesome-grid-cols-4 tablesome-gap-4">
                    <div class="tablesome-h-6 tablesome-bg-slate-200 tablesome-rounded tablesome-col-span-1"></div>
                    <div class="tablesome-h-6 tablesome-bg-slate-200 tablesome-rounded tablesome-col-span-1"></div>
                    <div class="tablesome-h-6 tablesome-bg-slate-200 tablesome-rounded tablesome-col-span-1"></div>
                    <div class="tablesome-h-6 tablesome-bg-slate-200 tablesome-rounded tablesome-col-span-1"></div>
                    </div>

                    <div class="tablesome-grid tablesome-grid-cols-4 tablesome-gap-4">
                    <div class="tablesome-h-6 tablesome-bg-slate-200 tablesome-rounded tablesome-col-span-1"></div>
                    <div class="tablesome-h-6 tablesome-bg-slate-200 tablesome-rounded tablesome-col-span-1"></div>
                    <div class="tablesome-h-6 tablesome-bg-slate-200 tablesome-rounded tablesome-col-span-1"></div>
                    <div class="tablesome-h-6 tablesome-bg-slate-200 tablesome-rounded tablesome-col-span-1"></div>
                    </div>

                    <div class="tablesome-flex tablesome-gap-4 tablesome-max-w-sm tablesome-mx-auto tablesome-justify-center">
                    <div class="tablesome-h-5 tablesome-w-5 tablesome-bg-slate-200 tablesome-rounded tablesome-col-span-1"></div>
                    <div class="tablesome-h-5 tablesome-w-5 tablesome-bg-slate-200 tablesome-rounded tablesome-col-span-1"></div>
                    <div class="tablesome-h-5 tablesome-w-5 tablesome-bg-slate-200 tablesome-rounded tablesome-col-span-1"></div>
                    <div class="tablesome-h-5 tablesome-w-5 tablesome-bg-slate-200 tablesome-rounded tablesome-col-span-1"></div>
                    <div class="tablesome-h-5 tablesome-w-5 tablesome-bg-slate-200 tablesome-rounded tablesome-col-span-1"></div>
                    <div class="tablesome-h-5 tablesome-w-5 tablesome-bg-slate-200 tablesome-rounded tablesome-col-span-1"></div>
                    </div>
                </div>
            </div>';

            return $html;
        }

        protected function get_filter_button_and_search($table_mode)
        {
            // if (!$this->collection['filter'] && !$this->collection['search'] && $table_mode == 'read-only') {
            //     return "";
            // }

            $html = "<div class='tablesome-header__wrapper'>";
            $html .= $this->get_search($table_mode);
            $html .= $this->get_filter_button($table_mode);
            $html .= "</div>";

            return $html;
        }

        protected function get_filter_button($table_mode)
        {
            $isReadOnlyMode = $table_mode == 'read-only';
            $desktopHide = !$this->collection["display"]["desktop-filter"] && $isReadOnlyMode;
            $modileHide = !$this->collection["display"]["mobile-filter"] && $isReadOnlyMode;

            $class = "";
            $class .= $desktopHide ? "lg-tablesome__filter--hide " : "";
            $class .= $modileHide ? "sm-tablesome__filter--hide " : "";

            $html = '<div class="tablesome__filter  ' . $class . '">';
            $html .= '<div class="tablesome__filter--button"><span class="dashicons dashicons-filter"></span> ' . __("FILTER", "tablesome") . '</div>';
            $html .= '</div>';
            $html .= '<div class="tablesome__options tablesome__options--hidden filter_options"></div>';

            return $html;
        }

        protected function get_search($table_mode)
        {
            $isReadOnlyMode = $table_mode == 'read-only';
            $desktopHide = !$this->collection["display"]["desktop-search"] && $isReadOnlyMode;
            $modileHide = !$this->collection["display"]["mobile-search"] && $isReadOnlyMode;

            $class = "";
            $class .= $desktopHide ? "lg-tablesome__search--hide " : "";
            $class .= $modileHide ? "sm-tablesome__search--hide " : "";

            $html = '<div class="tablesome__search  ' . $class . '">';
            $html .= '<label><span class="dashicons dashicons-search tablesome__search--icon"></span>';
            $html .= '<input type="text" placeholder="' . __("Type to Search ...", "tablesome") . '" name="search2" class="tablesome__search--input"></input></label>';
            $html .= '</div>';

            return $html;
        }

        protected function get_table_head($viewProps, $hide_table_header)
        {
            $html = '';
            if ($hide_table_header) {
                return $html;
            }

            $columns = isset($viewProps['items']['columns']) ? $viewProps['items']['columns'] : [];

            $html .= '<thead class="tablesome__thead">';
            $html .= '<tr class="tablesome__row">';
            if (isset($columns) && !empty($columns)) {
                foreach ($columns as $column) {
                    $html .= '<th class="tablesome__column"  data-column-id="' . $column['id'] . '" data-column-format="' . $column['format'] . '" ' . $this->tabIndex . '>';
                    $html .= '<span class="tablesome__column__name">' . $column['name'] . '</span>';
                    $html .= '<span class="tablesome__column__indicator"></span>';
                    $html .= '</th>';
                }
            }

            if (empty($columns)) {
                $html .= $this->get_empty_head();
            }

            $html .= '</tr>';
            $html .= '</thead>';

            return $html;
        }

        protected function get_table_body($viewProps)
        {

            $rows = isset($viewProps['items']['rows']) ? $viewProps['items']['rows'] : [];

            $html = '<tbody class="tablesome__tbody">';

            if (isset($rows) && !empty($rows)) {
                foreach ($rows as $row) {
                    $html .= $this->get_row($row);
                }
            }

            $html .= '</tbody>';

            return $html;
        }

        protected function get_table_foot($viewProps)
        {
            $html = '';
            if (!$viewProps['collection']['show_table_footer']) {

                $columns = isset($viewProps['items']['columns']) ? $viewProps['items']['columns'] : [];

                $html = '<tfoot class="tablesome-tfoot">';
                $html .= '<tr class="tablesome__row">';
                if (isset($columns) && !empty($columns)) {
                    foreach ($columns as $column) {
                        $html .= '<th  data-column-id="' . $column['id'] . '" class="tablesome__column" ' . $this->tabIndex . '>' . $column['name'] . '</th>';
                    }
                }

                if (empty($columns)) {
                    $html .= $this->get_empty_head();
                }

                $html .= '</tr>';
                $html .= '</tfoot>';
            }

            return $html;
        }

        private function get_row($row)
        {
            $record_id = isset($row['record_id']) ? $row['record_id'] : 0;
            $cells = isset($row['content']) ? $row['content'] : [];

            $html = '<tr class="tablesome__row" data-record-id="' . $record_id . '">';
            foreach ($cells as $cell) {

                $cell_html = (isset($cell['html']) && !empty($cell['html'])) ? $cell['html'] : "";
                $cell_html = (isset($cell['type']) && $cell["type"] == "textarea") ? '<div class="ql-editor">' . $cell["html"] . '</div>' : $cell_html;

                $html .= '<td ' . $this->tabIndex . ' class="tablesome__cell">' . $cell_html . '</td>';
            }
            $html .= '</tr>';

            return $html;
        }

        private function get_empty_head()
        {
            $html = '<th class="tablesome__column" ' . $this->tabIndex . ' data-column-format="text">';
            $html .= '<span class="tablesome__column__name">' . __("Column name", "tablesome") . '</span>';
            $html .= '</th>';
            return $html;
        }
    }
}
