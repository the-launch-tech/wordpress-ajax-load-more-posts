class PostAjax {
	constructor(load_more) {
    this.paged = 1;
    this.load_more = load_more;
    this.content = document.querySelector(`.${pa_.DOM.section_content}`);
    this.hidden_input = document.querySelector('input[name="max_num_pages"]');
    this.posts_per_page = this.load_more.getAttribute('data-posts-per-page');
    this.max_num_pages = this.hidden_input.value;
    this.post_type = this.load_more.getAttribute('data-post-type');

    this.checkPostStock();

    this.load_more.addEventListener('click', e =>
      this.sendAjax(e)
    );
	}

  sendAjax(e) {
    e.preventDefault();

    jQuery.ajax({
      url: pa_.url,
      dataType: 'json',
      type: 'GET',
      data: {
        action: pa_.action,
        security: pa_.security,
        paged: this.paged
      },
      success: res => this.handleSuccess(res),
      error: e => alert(e, pa_.error_message)
    });
  }

	handleSuccess(res) {
    this.paged = parseInt(res.paged) + 1;
		this.content.innerHTML += res.html;
    this.checkPostStock();
	}

  checkPostStock() {
    if (parseInt(this.paged) === parseInt(this.max_num_pages))
      this.load_more.style.display = 'none';
  }
}

window.addEventListener('load', () => {
  let load_more = document.getElementById(pa_.DOM.load_more),
      AJAX = load_more ? new PostAjax(load_more) : null;
} );
