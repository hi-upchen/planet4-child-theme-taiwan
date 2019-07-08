export default function() {
  const COOKIE_ID = 'gpea_tip_pledge_ids';
  let submitted_tips_ids = Cookies.getJSON(COOKIE_ID) || [];
  $('.tip-engage').each(function() {
    let form = $(this);
    let pid = form.find('input[name=pid]');
    if (pid.length && submitted_tips_ids.includes(pid.val())) {
      // form.find(':submit').attr('disabled', 'disabled');
      form.closest('.tip-action-buttons').addClass('has-committed');
    }
  });
  $('.tip-engage').on('submit', function(ev) {
    ev.preventDefault();
    let counter = false;
    let form = $(this);
    let query = form
      .children()
      .serializeArray()
      .reduce(function(acc, el) {
        return Object.assign(acc, { [el.name]: el.value });
      }, {});
    form.find(':submit').addClass('loading');
    if (query.pid) {
      counter = $(
        '#tip_commitments_post_' + query.pid + ' .js-tip-commitments'
      );
      submitted_tips_ids.push(query.pid);
      Cookies.set(COOKIE_ID, submitted_tips_ids, { expires: 365 });
    }
    $.ajax({
      url: window.localizations.ajaxurl,
      type: 'POST',
      data: {
        action: 'gpea_tips_pledge',
        query: query,
      },
      dataType: 'html',
    })
      .done(function(response) {
        counter && counter.text(parseInt(counter.text(), 10) + 1);
        form.find(':submit').removeClass('loading');
        form.find(':submit').attr('disabled', 'disabled');
        form.closest('.tip-action-buttons').addClass('has-committed');
      })
      .fail(function(jqXHR, textStatus, errorThrown) {
        form.find(':submit').removeClass('loading');
        console.error(errorThrown); // eslint-disable-line no-console
      });
    return false;
  });
}