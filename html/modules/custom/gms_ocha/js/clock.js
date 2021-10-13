(function($) {
    Drupal.behaviors.clock = {
        attach: function(context) {
            setInterval('Drupal.updateClock()', 1000);
        }
    };
    Drupal.updateClock = function() {
        var display = new Date(),
                minutes = display.getMinutes().toString().length == 1 ? '0' + display.getMinutes() : display.getMinutes(),
                hours = display.getHours().toString().length == 1 ? '0' + display.getHours() : display.getHours(),
                hour = hours > 12 ? hours - 12 : hours,
                seconds = display.getSeconds().toString().length == 1 ? '0' + display.getSeconds() : display.getSeconds(),
                ampm = display.getHours() >= 12 ? 'PM' : 'AM',
                months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        var currentTimeString = days[display.getDay()] + ', ' + months[display.getMonth()] + ' ' + display.getDate() + ', ' + display.getFullYear() + ' ' + hour + ':' + minutes + ':' + seconds + ' ' + ampm;
        $("#clock").html(currentTimeString);
    }
}(jQuery));