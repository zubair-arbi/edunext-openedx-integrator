<p>Example of usage:<br><pre>[edunext_enroll_button course_id="course-v1:Example+ID" label_enroll="Register" label_go_to_course="View Content" button_class_generic="dark"]</pre></p>
		<p>To specify a custom class for the container, button or color you may use the attributes 
			<strong>button_class_generic</strong>, <strong>container_class_generic</strong>, <strong>color_class_generic</strong>
		</p> 
		<p>There are 5 states you may use to customize the buttons on your post:</p>
		<ul>
			<li><strong>• enroll </strong><i>the course is open to enrollment and the user can click to enroll</i></li>
			<li><strong>• go_to_course </strong><i>the user is already enrolled and can click to open the course</i></li>
			<li><strong>• course_has_not_started </strong><i>the course has not started so user can't enroll yet, clicking does nothing</i></li>
			<li><strong>• invitation_only </strong><i>the course is invitation only but the user hasn't been invited, clicking does nothing</i></li>
			<li><strong>• enrollment_closed </strong><i>the course is already closed, clicking does nothing</i></li>
		</ul>
		<p>If for example you want to customize how it looks when an enrollment is on state "invitation_only" you may use the attributes 
		<strong>label_invitation_only</strong>, <strong>button_class_invitation_only</strong>, <strong>container_class_invitation_only</strong>, <strong>color_class_invitation_only</strong> like this:
		<pre>[edunext_enroll_button
    label_<strong>invitation_only</strong>="Sorry invitation only!"
    button_class_<strong>invitation_only</strong>="my-custom-button"
    container_class_<strong>invitation_only</strong>="my-custom-container"
    color_class_<strong>invitation_only</strong>="my-custom-color"]</pre>
    	In this example we are using the "invitation_only" state but you can use any other and it will work as expected.
		</p>
		<p>You may use the attribute <strong>hide_if="not logged in"</strong> if you want to hide the button when the user is NOT logged in. Inversely you may use the attribute <strong>hide_if="logged in"</strong> if you want to hide the button when the user is logged in</p>
		<script>
			jQuery(function ($) {
				var $metabox = $('#exo-shortcode-help');
				var $wpcontent = $("#wp-content-wrap");
				var $textarea = $('#html_text_area_id');

				$metabox.addClass('closed');
				var interval = setInterval(function () {
					var content;
					if ($wpcontent.hasClass("tmce-active")){
					    content = tinyMCE.activeEditor.getContent();
					} else {
					    content = $textarea.val() || '';
					}
					if (content.indexOf('[edun') !== -1) {
						if ($('.shine').length === 0) {
							$metabox.removeClass('closed').addClass('shine');
						}
					}
				}, 2000);
				setTimeout(function () {
					$('#exo-shortcode-help .ui-sortable-handle').click(function () {
						$('.shine').removeClass('shine');
						clearInterval(interval);
					});
				}, 1000);
			})
		</script>