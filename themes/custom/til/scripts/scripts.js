(function ($) {

  Drupal.behaviors.tilTimeAgo = {
    attach(context) {
      $('time.time-ago', context).once('tilTimeAgo').each(function() {
        var datetime = $(this).attr('datetime');
        $(this).text(moment().max(datetime).fromNow());
      });
    }
  };

})(jQuery);
