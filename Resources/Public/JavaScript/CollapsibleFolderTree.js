$('.fal-securedownload ul li span.icon-folder').on('click', function () {
  var $leaf = $(this).parent('li');
  if ($('span.icon-folder:first', $leaf).hasClass('icon-folder-open')) {
    $leaf.find('ul:first').slideUp('fast', function () {
      $('span.icon-folder:first', $leaf).removeClass('icon-folder-open')
    });
    $.get('/index.php?eID=FalSecuredownloadFileTreeState', {
      folder: $('span.icon-folder:first', $leaf).data('folder'),
      open: ''
    });
  } else {
    $leaf.find('ul:first').slideDown('fast', function () {
      $('span.icon-folder:first', $leaf).addClass('icon-folder-open')
    });
    $.get('/index.php?eID=FalSecuredownloadFileTreeState', {
      folder: $('span.icon-folder:first', $leaf).data('folder'),
      open: 1
    });
  }
}).each(function () {
  $(this).next('ul').hide();
  if ($(this).hasClass('icon-folder-open')) {
    $(this).next('ul').slideDown();
  }
});
