$('.fal-securedownload ul li span.icon-folder').on('click', function() {
    var $leef = $(this).parent('li');
    if ($('span.icon-folder:first', $leef).hasClass('icon-folder-open')) {
        $leef.find('ul:first').slideUp('fast', function(){$('span.icon-folder:first', $leef).removeClass('icon-folder-open')});
        $.get('/index.php?eID=FalSecuredownloadFileTreeState',{
            folder: $('span.icon-folder:first', $leef).data('folder'),
            open: ''
        });
    } else {
        $leef.find('ul:first').slideDown('fast', function(){$('span.icon-folder:first', $leef).addClass('icon-folder-open')});
        $.get('/index.php?eID=FalSecuredownloadFileTreeState',{
            folder: $('span.icon-folder:first', $leef).data('folder'),
            open: 1
        });
    }
});
$('.fal-securedownload ul li span.icon-folder').each(function() {
    $(this).next('ul').hide();
    if ($(this).hasClass('icon-folder-open')) {
        $(this).next('ul').slideDown();
    }
});
