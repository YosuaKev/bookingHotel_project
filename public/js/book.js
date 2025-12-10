$(function(){
    $(document).on('submit', '#book_now_form', function(e){
        e.preventDefault();
        var arrival = $(this).find('input[name="arrival"]').val();
        var departure = $(this).find('input[name="departure"]').val();

        if(!arrival || !departure){
            if(window.Theme && Theme.toast) Theme.toast('Please choose both arrival and departure dates');
            else alert('Please choose both arrival and departure dates.');
            return;
        }

        var a = new Date(arrival);
        var d = new Date(departure);
        if(isNaN(a.getTime()) || isNaN(d.getTime())){
            if(window.Theme && Theme.toast) Theme.toast('Please provide valid dates');
            else alert('Please provide valid dates.');
            return;
        }
        if(a > d){
            if(window.Theme && Theme.toast) Theme.toast('Departure must be the same or after arrival');
            else alert('Departure must be the same or after arrival.');
            return;
        }

        // Redirect to booking page with query params
        var params = '?arrival=' + encodeURIComponent(arrival) + '&departure=' + encodeURIComponent(departure);
        window.location.href = 'booking.html' + params;
    });
});
