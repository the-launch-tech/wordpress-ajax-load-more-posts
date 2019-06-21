To leverage this in a theme do the following:

1. Require the PHP class in your functions.php file within your theme or child theme.
require $this->CLASSES . 'post-ajax/post-ajax.php';

2. Render the section within a page.
$Post_Ajax = Post_Ajax::initialize_class();
$Post_Ajax->render_post_ajax_section();

That's it!

You could add custom fields to expose options to the admin, and do a number of other things, but this is a good starting point!
