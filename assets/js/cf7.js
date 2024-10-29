/**
 * Advanced Custom Data
 * 
 * Support to Contact Form 7 - 5.9.8
 */
jQuery(function($){

    $(window).on('load', function(){
        let html = $('#acd-wpcf7-template').html(), 
            types = [
                'menu',
                'checkbox',
                'radio'
            ];

        if(html == '') return;

        $('.tag-generator-panel').each(function(){
            let f = $(this);

            if(types.indexOf(f.data('id')) > -1) {
                $('[name="values"]',this).closest('tr').after(html);
            }
        });
    });

    $(document).on('change', '.js-acd-data-id', function(){
        let p = $(this), 
            input = p.closest('.tag-generator-panel').find('[name="values"]');
        
        if(input.length>0) {
            input.val( p.val() );
        }
    });

});