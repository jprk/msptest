{literal}
<script src="//cdn.tinymce.com/4/tinymce.min.js"></script>
<script>tinymce.init({
        selector:'textarea',
        plugins: [
            'advlist autolink lists link image charmap print preview anchor',
            'searchreplace visualblocks code fullscreen',
            'insertdatetime media table contextmenu paste code'
        ],
        toolbar: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image',
        style_formats: [
            {title: 'Inactive text', classes: 'inactive_text'},
            {title: 'Alert text', classes: 'alert_text'},
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
        invalid_styles: {
            'tr' : 'width height',
            'th' : 'width height',
            'td' : 'width height'
        }
    });</script>
{/literal}
