jQuery(document).ready(function($) {
    if (typeof promotionCountdownData !== 'undefined') {
        var countdownContainer = $(promotionCountdownData.selector);
        var endDate = new Date(promotionCountdownData.expirationDate).getTime();

        var countdownFunction = function() {
            var now = new Date().getTime();
            var timeleft = endDate - now;
            
            var days = Math.floor(timeleft / (1000 * 60 * 60 * 24));
            var hours = Math.floor((timeleft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((timeleft % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((timeleft % (1000 * 60)) / 1000);
            
            // Use localized strings
            countdownContainer.html(days + " " + promotionCountdownStrings.days + " " +
                                    hours + " " + promotionCountdownStrings.hours + " " +
                                    minutes + " " + promotionCountdownStrings.minutes + " " +
                                    seconds + " " + promotionCountdownStrings.seconds);
            
            if (timeleft < 0) {
                clearInterval(x);
                countdownContainer.html(promotionCountdownStrings.expired);
            }
        };

        var x = setInterval(countdownFunction, 1000);
    }
});
