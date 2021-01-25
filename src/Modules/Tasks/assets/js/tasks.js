var tasksJs = {
    onReady: function(){
        $(function () {
            var val;
            var minute = $("#minute");
            var hour = $("#hour");
            var day = $("#day");
            var month = $("#month");
            var weekday = $("#weekday");

            var commonOptions = $("#commonOptions");
            var minuteOptions = $("#minuteOptions");
            var hourOptions = $("#hourOptions");
            var dayOptions = $("#dayOptions");
            var monthOptions = $("#monthOptions");
            var weekdayOptions = $("#weekdayOptions");

            $(commonOptions).change(function () {
                val = $(this).val();
                val = val.split(" ");
                $(minute).val(val[0]);
                $(hour).val(val[1]);
                $(day).val(val[2]);
                $(month).val(val[3]);
                $(weekday).val(val[4]);
            });
            $(minuteOptions).change(function () {
                val = $(this).val();
                if (val == '---') {
                    val = '';
                }
                $(minute).val(val);
            });
            $(hourOptions).change(function () {
                val = $(this).val();
                if (val == '---') {
                    val = '';
                }
                $(hour).val(val);
            });
            $(dayOptions).change(function () {
                val = $(this).val();
                if (val == '---') {
                    val = '';
                }
                $(day).val(val);
            });
            $(monthOptions).change(function () {
                val = $(this).val();
                if (val == '---') {
                    val = '';
                }
                $(month).val(val);
            });
            $(weekdayOptions).change(function () {
                val = $(this).val();
                if (val == '---') {
                    val = '';
                }
                $(weekday).val(val);
            });

        });
    }
};

$(document).ready(function(){
    tasksJs.onReady();
});
