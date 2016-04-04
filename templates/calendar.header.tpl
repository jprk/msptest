<script type="text/javascript" src="./js/jquery.js"></script>
<script type="text/javascript" src="./js/moment-with-locales.js"></script>
{*<script type="text/javascript" src="./bootstrap/js/transition.js"></script>*}
{*<script type="text/javascript" src="./bootstrap/js/collapse.js"></script>*}
<script type="text/javascript" src="./bootstrap/js/bootstrap.min.js"></script>
<script type="text/javascript" src="./bootstrap/js/bootstrap-datetimepicker.js"></script>
<link rel="stylesheet" href="./bootstrap/css/bootstrap-datetimepicker.min.css" />
<link rel="stylesheet" href="./bootstrap/css/bootstrap.min.css" />
{*<script type="text/javascript">{literal}
    ko.bindingHandlers.dateTimePicker = {
        init: function (element, valueAccessor, allBindingsAccessor) {
            //initialize datepicker with some optional options
            var options = allBindingsAccessor().dateTimePickerOptions || {};
            $(element).datetimepicker(options);

            //when a user changes the date, update the view model
            ko.utils.registerEventHandler(element, "dp.change", function (event) {
                var value = valueAccessor();
                if (ko.isObservable(value)) {
                    if (event.date != null && !(event.date instanceof Date)) {
                        value(event.date.toDate());
                    } else {
                        value(event.date);
                    }
                }
            });

            ko.utils.domNodeDisposal.addDisposeCallback(element, function () {
                var picker = $(element).data("DateTimePicker");
                if (picker) {
                    picker.destroy();
                }
            });
        },
        update: function (element, valueAccessor, allBindings, viewModel, bindingContext) {

            var picker = $(element).data("DateTimePicker");
            //when the view model is updated, update the widget
            if (picker) {
                var koDate = ko.utils.unwrapObservable(valueAccessor());

                //in case return from server datetime i am get in this form for example /Date(93989393)/ then fomat this
                koDate = (typeof (koDate) !== 'object') ? new Date(parseFloat(koDate.replace(/[^0-9]/g, ''))) : koDate;

                picker.date(koDate);
            }
        }
    };
{/literal}</script>*}
{literal}
<style>
.date .input-override {
    padding: 0px 4px;
    height: auto;
    font-size: 12px;
    border: 1px solid gray;
    color: black;
    border-radius: initial;
    width: 18ex;
}
.glyphicon-override {
    padding: 0px 0px 3px 6px;
    border: none;
    background: none;
}
.admintable.table-override {
    border-color: black;
    border-spacing: 1px;
    border-collapse: initial;
}
</style>{/literal}