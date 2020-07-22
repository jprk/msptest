{*
<script src="//cdn.tinymce.com/4/tinymce.min.js"></script>
*}
{literal}
    <script src="https://cdn.tiny.cloud/1/0a9xak5n6dd7felvhf8hsj7jo41treglskjrw53enhzsnal2/tinymce/5/tinymce.min.js"
            referrerpolicy="origin"></script>
    <script>tinymce.init({
            entity_encoding: 'raw',
            selector: 'textarea',
            plugins: [
                'advlist autolink lists link image charmap print preview anchor',
                'searchreplace visualblocks code fullscreen',
                'insertdatetime media table paste code' // contextmenu in tinymce 4
            ],
            toolbar: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image',
            style_formats: [
                {title: 'Inactive text', classes: 'inactive_text'},
                {title: 'Alert text', classes: 'alert_text'},
                {title: 'Online teaching block', block: 'div', classes: 'onlineteaching', wrapper: true},
                {title: 'Table styles'},
                {title: 'Narrow table', selector: 'table', classes: 'narrowtable'},
                {title: 'Section table (full width)', selector: 'table', classes: 'sectiontable'},
                {title: 'Table row white', selector: 'tr', classes: 'rowWhite'},
                {title: 'Table row red', selector: 'tr', classes: 'rowRed'},
                {title: 'Table row A', selector: 'tr', classes: 'rowA'},
                {title: 'Table row B', selector: 'tr', classes: 'rowB'},
                {title: 'Table cell gray left aligned', selector: 'td', classes: 'grayLeft'},
                {title: 'Table cell gray right aligned', selector: 'td', classes: 'grayRight'},
                {title: 'Table cell week number', selector: 'td', classes: 'week'}
            ],
            content_css: ['stylist.css', /* 'style.css' */],
            invalid_styles: {
                'table': 'width height',
                'tr': 'width height',
                'th': 'width height',
                'td': 'width height'
            }
        });</script>
{/literal}
