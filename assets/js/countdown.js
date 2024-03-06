jQuery(document).ready(function($) {
    if (typeof promotionCountdownData !== 'undefined') {
        var countdownContainer = $(promotionCountdownData.selector);
        var endDate = new Date(promotionCountdownData.expirationDate).getTime();

        var countdownFunction = function() {
            var now = new Date().getTime();
            var timeleft = endDate - now;
            
            // Calculating the days, hours, minutes and seconds left
            var days = Math.floor(timeleft / (1000 * 60 * 60 * 24));
            var hours = Math.floor((timeleft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((timeleft % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((timeleft % (1000 * 60)) / 1000);
            
            // Result is output to the specific element
            countdownContainer.html(days + "d " + hours + "h " + minutes + "m " + seconds + "s ");
            
            // If the count down is finished, write some text 
            if (timeleft < 0) {
                clearInterval(x);
                countdownContainer.html("EXPIRED");
            }
        };

        // Update the count down every 1 second
        var x = setInterval(countdownFunction, 1000);
    }
});
