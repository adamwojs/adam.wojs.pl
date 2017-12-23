(function() {
    var sidebar, content;
    
    var filter = function(tag) {
        if(tag === "") {
            // Wyświetlamy wszystkie projekty
            $('.project', content).show();
            
            return ;
        }        
        
        $('.project', content).each(function() {
            var isMatch = $(this).attr('data-tags')
                    .split(',')
                    .indexOf(tag) !== -1;

            isMatch ? $(this).show() : $(this).hide();
        });          
    };
    
    $(document).ready(function() {
        sidebar = $('#sidebar-outer');
        content = $('.content-outer');

        // Pokaż / ukryj sidebar 
        $('.navbar-header').click(function() {
            var l = '0%';
            var w = sidebar.width();
            if(sidebar.hasClass('sidebar-visible')) {
                l = '-100%';
                w = 0;
            }

            content.animate({'margin-left': w});
            sidebar.animate({'left': l}, function() {
                $(this).toggleClass('sidebar-visible');
            });
        });

        // Kliknięcie poza sidebaren, gdy jest widoczny powoduje jego ukrycie
        $(content).click(function() {
            if(sidebar.hasClass('sidebar-visible')) {
                $('.navbar-header').click();

                return false;
            }
        });

        $('select.filters').change(function() {
            filter($(this).val());
        });

        // Filtrowanie portfolio
        $('nav.filters a').click(function(e) {
            e.preventDefault();

            // Oznaczamy tag jako aktywny
            $(this).tab('show');

            filter($(this).attr('data-tag'));
        });  

        // Animacja paska umiejętności 
        $('.progress-bar', content).each(function() {
            var self = $(this);
            var val  = self.width();

            self.css('margin-left', -val);
            self.animate({ 'margin-left': 0 }, 1000);            
        });        
    });
}) ();

