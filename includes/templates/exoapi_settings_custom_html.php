<style>
.eoxapis-container {
	position: relative;
	max-width: 631px;
}
.eoxapis-container > div {
    display: inline-block;
    background: white;
    padding: 0 20px 15px 20px;
    border: 1px solid #CCC;
    border-radius: 3px;
    position: relative;
}
.eoxapis-container .json-example {
	display: inline-block;
	background: #DDD;
	font-family: monospace;
	padding: 4px 2px;
	line-height: 1.5em;
	font-size: 11px;
	position: absolute;
	top: 0;
	left: 0;
	border-radius: 3px;
	padding: 5px;
}
.eoxapis-container .json-example-container {
	position: relative;
}
.eoxapis-container .json-toggle {
	text-decoration: underline;
	cursor: help;
}
.eoxapis-container .button-secondary {
	display: block;
	margin-top: 12px;
	margin-left: auto;
}
.eoxapis-container textarea {
	font-family: monospace;
}
#eox-select-api {
	position: absolute;
	top: 15px;
	right: 20px;
	z-index: 3;
	width: 130px;
}
</style>
<div class="eoxapis-container">
	<select name="" id="eox-select-api">
		<option value="exoapi-add-new-users" selected>Users</option>
		<option value="exoapi-add-new-enrollments">Enrollments</option>
		<option value="exoapi-get-users">Get users info</option>
	</select>
	<div class="exoapi-get-users">
		<h2>Get Open edx users info</h2>
		<p>
			Write an array of users as JSON array:
		</p>
		<textarea name="eox-api-get-users" id="eox-api-get-users" cols="70" rows="10">
[{
    "username": "honor"
}]</textarea>
		<button class="button-secondary get-users-button">Execute API call</button>
	</div>
	<div class="exoapi-add-new-users">
		<h2>Add new Open edx users</h2>
		<p>
			Write the new users info using a JSON array:
		</p>
		<textarea name="eox-api-new-users" id="eox-api-new-users" cols="70" rows="10">
[{
    "email": "honor@example.com",
    "username": "honor",
    "password": "edx",
    "fullname": "Honor McGregor",
    "activate_user": true
}]</textarea>
		<button class="button-secondary save-users-button">Execute API call</button>
	</div>
	<div class="exoapi-add-new-enrollments">
		<h2>Add new Enrollments</h2>
		<p>
			Write the new enrollments info using a JSON array:
		</p>
		<textarea name="eox-api-new-enrollments" id="eox-api-new-enrollments" cols="70" rows="10">
[{
    "username": "honor",
    "course_id": "course-v1:edX+DemoX+Demo_Course",
    "mode": "audit"
}]</textarea>
		<button class="button-secondary save-enrollments-button">Execute API call</button>
	</div>
</div>
<script>
jQuery(function ($) {

	var callAction = function (action, data_arg) {
		var data = {
			'action': action,
			'_ajax_nonce': "<?= wp_create_nonce( 'eoxapi' ); ?>"
		};
		Object.assign(data, data_arg);
		jQuery.post(ajaxurl, data, function(html) {
			$('.notice').remove();
			$('#wp-edunext-marketing-site_settings > h2').first().after(html);
		});
	}

	$('.json-toggle').on('click', function () {
		$('.json-example').value()
	});

	$('.save-users-button').click(function (e) {
		var data = {users: $('#eox-api-new-users').val()};
		callAction('save_users_ajax', data);
		e.stopPropagation();
		return false;
	});

	$('.get-users-button').click(function (e) {
		var data = {users: $('#eox-api-get-users').val()};
		callAction('get_users_ajax', data);
		e.stopPropagation();
		return false;
	});

	$('.save-enrollments-button').click(function (e) {
		var data = {enrollments: $('#eox-api-new-enrollments').val()};
		callAction('save_enrollments_ajax', data);
		e.stopPropagation();
		return false;
	});

	$('#eox-select-api').on('change', function () {
		$('.' + this.value).show().siblings('div').hide();
	});

	$('#eox-select-api').change();
})
</script>
