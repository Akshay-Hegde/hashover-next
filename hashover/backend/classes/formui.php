<?php namespace HashOver;

// Copyright (C) 2015-2018 Jacob Barkdull
// This file is part of HashOver.
//
// HashOver is free software: you can redistribute it and/or modify
// it under the terms of the GNU Affero General Public License as
// published by the Free Software Foundation, either version 3 of the
// License, or (at your option) any later version.
//
// HashOver is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Affero General Public License for more details.
//
// You should have received a copy of the GNU Affero General Public License
// along with HashOver.  If not, see <http://www.gnu.org/licenses/>.


class FormUI
{
	public $setup;
	public $mode;
	public $locale;
	public $avatars;
	public $misc;
	public $cookies;
	public $login;
	public $commentCounts;
	public $pageTitle;
	public $pageURL;
	public $postCommentOn;
	public $popularComments;
	public $comments;

	protected $emphasizedField;
	protected $defaultLoginInputs;

	public function __construct (Setup $setup, array $counts)
	{
		$this->setup = $setup;
		$this->mode = $setup->usage['mode'];
		$this->locale = new Locale ($setup);
		$this->login = new Login ($setup);
		$this->avatars = new Avatars ($setup);
		$this->cookies = new Cookies ($setup);
		$this->commentCounts = $counts;
		$this->pageTitle = $this->setup->pageTitle;
		$this->pageURL = $this->setup->pageURL;

		// Attempt to get form field submission failed on
		$failedField = $this->cookies->getValue ('failed-on');

		// Set the field to emphasize after a failed post
		if ($failedField !== null) {
			$this->emphasizedField = $failedField;
		}

		// "Post a comment" locale strings
		$post_comment_on = $this->locale->text['post-comment-on'];
		$this->postCommentOn = $post_comment_on[0];

		// Add optional "on <page title>" to "Post a comment" title
		if ($this->setup->displaysTitle !== false and !empty ($this->pageTitle)) {
			$this->postCommentOn = sprintf ($post_comment_on[1], $this->pageTitle);
		}

		// Create default login inputs elements
		$this->defaultLoginInputs = $this->loginInputs ();
	}

	// Re-encode a URL
	protected function safeURLEncode ($url)
	{
		return urlencode (urldecode ($url));
	}

	// Creates input elements for user login information
	protected function loginInputs ($permalink = '', $edit_form = false, $name = '', $website = '')
	{
		$permalink = !empty ($permalink) ? '-' . $permalink : '';

		// Login input attribute information
		$login_input_attributes = array (
			'name' => array (
				'wrapper-class' => 'hashover-name-input',
				'label-class' => 'hashover-name-label',
				'placeholder' => $this->locale->text['name'],
				'input-id' => 'hashover-main-name' . $permalink,
				'input-type' => 'text',
				'input-name' => 'name',
				'input-title' => $this->locale->text['name-tip'],
				'input-value' => Misc::makeXSSsafe ($this->login->name)
			),

			'password' => array (
				'wrapper-class' => 'hashover-password-input',
				'label-class' => 'hashover-password-label',
				'placeholder' => $this->locale->text['password'],
				'input-id' => 'hashover-main-password' . $permalink,
				'input-type' => 'password',
				'input-name' => 'password',
				'input-title' => $this->locale->text['password-tip'],
				'input-value' => ''
			),

			'email' => array (
				'wrapper-class' => 'hashover-email-input',
				'label-class' => 'hashover-email-label',
				'placeholder' => $this->locale->text['email'],
				'input-id' => 'hashover-main-email' . $permalink,
				'input-type' => 'email',
				'input-name' => 'email',
				'input-title' => $this->locale->text['email-tip'],
				'input-value' => Misc::makeXSSsafe ($this->login->email)
			),

			'website' => array (
				'wrapper-class' => 'hashover-website-input',
				'label-class' => 'hashover-website-label',
				'placeholder' => $this->locale->text['website'],
				'input-id' => 'hashover-main-website' . $permalink,
				'input-type' => 'url',
				'input-name' => 'website',
				'input-title' => $this->locale->text['website-tip'],
				'input-value' => Misc::makeXSSsafe ($this->login->website)
			)
		);

		// Change input values to specified values
		if ($edit_form === true) {
			$login_input_attributes['name']['input-value'] = $name;
			$login_input_attributes['password']['placeholder'] = $this->locale->text['confirm-password'];
			$login_input_attributes['password']['input-title'] = $this->locale->text['confirm-password'];
			$login_input_attributes['website']['input-value'] = $website;
		}

		// Create wrapper element for styling login inputs
		$login_inputs = new HTMLTag ('div', array (
			'class' => 'hashover-inputs'
		));

		// Create and append login input elements to main form inputs wrapper element
		foreach ($login_input_attributes as $field => $attributes) {
			// Skip disabled input tags
			if ($this->setup->fieldOptions[$field] === false) {
				continue;
			}

			// Create cell element for inputs
			$input_cell = new HTMLTag ('div', array (
				'class' => 'hashover-input-cell'
			));

			if ($this->setup->usesLabels === true) {
				// Create label element for input
				$label = new HTMLTag ('label', array (
					'for' => $attributes['input-id'],
					'class' => $attributes['label-class'],
					'innerHTML' => $attributes['placeholder']
				), false);

				// Add label to cell element
				$input_cell->appendChild ($label);
			}

			// Create wrapper element for input
			$input_wrapper = new HTMLTag ('div', array (
				'class' => $attributes['wrapper-class']
			));

			// Add a class for indicating a required field
			if ($this->setup->fieldOptions[$field] === 'required') {
				$input_wrapper->appendAttribute ('class', 'hashover-required-input');
			}

			// Add a class for indicating a post failure
			if ($this->emphasizedField === $field) {
				$input_wrapper->appendAttribute ('class', 'hashover-emphasized-input');
			}

			// Create input element
			$input = new HTMLTag ('input', array (
				'id' => $attributes['input-id'],
				'class' => 'hashover-input-info',
				'type' => $attributes['input-type'],
				'name' => $attributes['input-name'],
				'value' => $attributes['input-value'],
				'title' => $attributes['input-title'],
				'placeholder' => $attributes['placeholder']
			), false, true);

			// Add input to wrapper element
			$input_wrapper->appendChild ($input);

			// Add input to cell element
			$input_cell->appendChild ($input_wrapper);

			// Add input cell to main inputs wrapper element
			$login_inputs->appendChild ($input_cell);
		}

		return $login_inputs;
	}

	protected function avatar ($text)
	{
		// If avatars set to images
		if ($this->setup->iconMode === 'image') {
			// Logged in
			if ($this->login->userIsLoggedIn === true) {
				// Image source is avatar image
				$hash = !empty ($this->login->email) ? md5 (mb_strtolower (trim ($this->login->email))) : '';
				$avatar_src = $this->avatars->getGravatar ($hash);
			} else {
				// Logged out
				// Image source is local default image
				$avatar_src = $this->setup->getImagePath ('first-comment');
			}

			// Create avatar image element
			$avatar = new HTMLTag ('div', array (
				'style' => 'background-image: url(\'' . $avatar_src . '\');'
			), false);
		} else {
			// Avatars set to count
			// Create element displaying comment number user will be
			$avatar = new HTMLTag ('span', $text, false);
		}

		return $avatar;
	}

	protected function subscribeLabel ($id = '', $type = 'main', $checked = true)
	{
		// Create subscribe checkbox label element
		$subscribe_label = new HTMLTag ('label', array (
			'for' => 'hashover-' . $type . '-subscribe',
			'class' => 'hashover-' . $type . '-label',
			'title' => $this->locale->text['subscribe-tip']
		));

		if (!empty ($id)) {
			$subscribe_label->appendAttribute ('for', '-' . $id, false);
		}

		// Create subscribe element checkbox
		$subscribe = new HTMLTag ('input', array (
			'id' => 'hashover-' . $type . '-subscribe',
			'type' => 'checkbox',
			'name' => 'subscribe'
		), false, true);

		if (!empty ($id)) {
			$subscribe->appendAttribute ('id', '-' . $id, false);
		}

		// Check checkbox
		if ($checked === true) {
			$subscribe->createAttribute ('checked', 'true');
		}

		// Add subscribe checkbox element to subscribe checkbox label element
		$subscribe_label->appendChild ($subscribe);

		// Add text to subscribe checkbox label element
		$subscribe_label->appendInnerHTML ($this->locale->text['subscribe']);

		return $subscribe_label;
	}

	protected function acceptedFormatCell ($format, $locale_key)
	{
		$title = new HTMLTag ('p', array ('class' => 'hashover-title'));
		$accepted_format = sprintf ($this->locale->text['accepted-format'], $format);
		$title->innerHTML ($accepted_format);

		$paragraph = new HTMLTag ('p');
		$paragraph->innerHTML ($this->locale->text[$locale_key]);

		return new HTMLTag ('div', array (
			'children' => array ($title, $paragraph)
		));
	}

	protected function commentForm (HTMLTag $form, $type, $placeholder, $text, $permalink = '')
	{
		$permalink = !empty ($permalink) ? '-' . $permalink : '';
		$title_locale = ($type === 'reply') ? 'reply-form' : 'comment-form';

		// Create textarea
		$textarea = new HTMLTag ('textarea', array (
			'id' => 'hashover-' . $type . '-comment' . $permalink,
			'class' => 'hashover-textarea hashover-' . $type . '-textarea',
			'cols' => '63',
			'name' => 'comment',
			'rows' => '6',
			'title' => $this->locale->text[$title_locale]
		), false);

		// Set the placeholder attribute if one is given
		if (!empty ($placeholder)) {
			$textarea->createAttribute ('placeholder', $placeholder);
		}

		if ($type === 'main') {
			// Add a class for indicating a post failure
			if ($this->emphasizedField === 'comment') {
				$textarea->appendAttribute ('class', 'hashover-emphasized-input');
			}

			// If the comment was a reply, have the textarea use the reply textarea locale
			if ($this->cookies->getValue ('replied') !== null) {
				$reply_form_placeholder = $this->locale->text['reply-form'];
				$textarea->createAttribute ('placeholder', $reply_form_placeholder);
			}
		}

		// Set textarea content if given any text
		if (!empty ($text)) {
			$textarea->innerHTML ($text);
		}

		// Add textarea element to form element
		$form->appendChild ($textarea);

		// Create element for various messages when needed
		if ($type !== 'main') {
			$message = new HTMLTag ('div', array (
				'id' => 'hashover-' . $type . '-message-container' . $permalink,
				'class' => 'hashover-message',

				'children' => array (
					new HTMLTag ('div', array (
						'id' => 'hashover-' . $type . '-message' . $permalink
					))
				)
			));

			// Add message element to form element
			$form->appendChild ($message);
		}

		// Create accepted HTML message element
		$accepted_formatting_message = new HTMLTag ('div', array (
			'id' => 'hashover-' . $type . '-formatting-message' . $permalink,
			'class' => 'hashover-formatting-message'
		));

		// Create formatting table
		$accepted_formatting_table = new HTMLTag ('div', array (
			'class' => 'hashover-formatting-table',

			'children' => array (
				$this->acceptedFormatCell ('HTML', 'accepted-html')
			)
		));

		// Append Markdown cell if Markdown is enabled
		if ($this->setup->usesMarkdown !== false) {
			$markdown_cell = $this->acceptedFormatCell ('Markdown', 'accepted-markdown');
			$accepted_formatting_table->appendChild ($markdown_cell);
		}

		// Append formatting table to formatting message
		$accepted_formatting_message->appendChild ($accepted_formatting_table);

		// Ensure the accepted HTML message is open in PHP mode
		if ($this->mode === 'php') {
			$accepted_formatting_message->appendAttribute ('class', 'hashover-message-open');
			$accepted_formatting_message->appendAttribute ('class', 'hashover-php-message-open');
		}

		// Add accepted HTML message element to form element
		$form->appendChild ($accepted_formatting_message);
	}

	protected function pageInfoFields (HTMLTag $form)
	{
		// Create hidden comment thread input element
		$thread_input = new HTMLTag ('input', array (
			'type' => 'hidden',
			'name' => 'thread',
			'value' => $this->setup->threadName
		), false, true);

		// Add hidden comments thread input element to form element
		$form->appendChild ($thread_input);

		// Create hidden page URL input element
		$url_input = new HTMLTag ('input', array (
			'type' => 'hidden',
			'name' => 'url',
			'value' => $this->pageURL
		), false, true);

		// Add hidden page URL input element to form element
		$form->appendChild ($url_input);

		// Create hidden page title input element
		$title_input = new HTMLTag ('input', array (
			'type' => 'hidden',
			'name' => 'title',
			'value' => $this->pageTitle
		), false, true);

		// Add hidden page title input element to form element
		$form->appendChild ($title_input);

		// Check if the script is being remotely accessed
		if ($this->setup->remoteAccess === true) {
			// Create hidden input element indicating remote access
			$remote_access_input = new HTMLTag ('input', array (
				'type' => 'hidden',
				'name' => 'remote-access',
				'value' => 'true'
			), false, true);

			// Add remote access input element to form element
			$form->appendChild ($remote_access_input);
		}
	}

	protected function acceptedFormatting ($type, $permalink = '')
	{
		$permalink = !empty ($permalink) ? '-' . $permalink : '';
		$accepted_format = $this->locale->text['comment-formatting'];

		// Create accepted HTML message revealer hyperlink
		$accepted_formatting = new HTMLTag ('span', array (
			'id' => 'hashover-' . $type . '-formatting' . $permalink,
			'class' => 'hashover-fake-link hashover-formatting',
			'title' => $accepted_format,
			'innerHTML' => $accepted_format
		));

		// Return the hyperlink
		return $accepted_formatting;
	}

	public function initialHTML ($hashover_wrapper = true)
	{
		// Create element that HashOver comments will appear in
		$hashover_element = new HTMLTag ('div', array (
			'id' => 'hashover',
			'class' => 'hashover'
		), false);

		// Add class indictating desktop and mobile styling
		if ($this->setup->isMobile === true) {
			$hashover_element->appendAttribute ('class', 'hashover-mobile');
		} else {
			$hashover_element->appendAttribute ('class', 'hashover-desktop');
		}

		// Add class for raster or vector images
		if ($this->setup->imageFormat === 'svg') {
			$hashover_element->appendAttribute ('class', 'hashover-vector');
		} else {
			$hashover_element->appendAttribute ('class', 'hashover-raster');
		}

		// Add class to indicate user login status
		if ($this->login->userIsLoggedIn === true) {
			$hashover_element->appendAttribute ('class', 'hashover-logged-in');
		} else {
			$hashover_element->appendAttribute ('class', 'hashover-logged-out');
		}

		// Create element for jump anchor
		$jump_anchor = new HTMLTag ('div', array (
			'id' => 'comments'
		));

		// Add jump anchor to HashOver element
		$hashover_element->appendChild ($jump_anchor);

		// Create primary form wrapper element
		$form_section = new HTMLTag ('div', array (
			'id' => 'hashover-form-section'
		));

		// Hide primary form wrapper if comments are to be initially hidden
		if ($this->mode !== 'php' and $this->setup->collapsesInterface === true) {
			$form_section->createAttribute ('style', 'display: none;');
		}

		// Create element for "Post Comment" title
		$post_title = new HTMLTag ('span', array (
			'class' => array (
				'hashover-title',
				'hashover-main-title',
				'hashover-dashed-title'
			),

			'innerHTML' => $this->postCommentOn
		));

		// Add "Post Comment" element to primary form wrapper
		$form_section->appendChild ($post_title);

		// Create element for various messages when needed
		$message_container = new HTMLTag ('div', array (
			'id' => 'hashover-message-container',
			'class' => 'hashover-title hashover-message'
		));

		// Create element for message text
		$message_element = new HTMLTag ('div', array (
			'id' => 'hashover-message'
		));

		// Check if message cookie is set
		if ($this->cookies->getValue ('message') !== null
		    or $this->cookies->getValue ('error') !== null)
		{
			// If so, set the message element to open in PHP mode
			if ($this->mode === 'php') {
				$message_container->appendAttribute ('class', array (
					'hashover-message-open',
					'hashover-php-message-open'
				));
			}

			// Check if the message is a normal message
			if ($this->cookies->getValue ('message') !== null) {
				// If so, get an XSS safe version of the message
				$message = Misc::makeXSSsafe ($this->cookies->getValue ('message'));
			} else {
				// If not, get an XSS safe version of the error message
				$message = Misc::makeXSSsafe ($this->cookies->getValue ('error'));

				// And set a class to the message element indicating an error
				$message_container->appendAttribute ('class', 'hashover-message-error');
			}

			// And put current message into message element
			$message_element->innerHTML ($message);
		}

		// Add message text element to message element
		$message_container->appendChild ($message_element);

		// Add message element to primary form wrapper
		$form_section->appendChild ($message_container);

		// Create main HashOver form
		$main_form = new HTMLTag ('form', array (
			'id' => 'hashover-form',
			'class' => 'hashover-balloon',
			'name' => 'hashover-form',
			'action' => $this->setup->getBackendPath ('form-actions.php'),
			'method' => 'post'
		));

		// Create wrapper element for styling inputs
		$main_inputs = new HTMLTag ('div', array (
			'class' => 'hashover-inputs'
		));

		// If avatars are enabled
		if ($this->setup->iconMode !== 'none') {
			// Create avatar element for main HashOver form
			$main_avatar = new HTMLTag ('div', array (
				'class' => 'hashover-avatar-image'
			));

			// Add count element to avatar element
			$main_avatar->appendChild ($this->avatar ($this->commentCounts['primary']));

			// Add avatar element to inputs wrapper element
			$main_inputs->appendChild ($main_avatar);
		}

		// Logged in
		if ($this->login->userIsLoggedIn === true) {
			if (!empty ($this->login->name)) {
				$user_name = Misc::makeXSSsafe ($this->login->name);
			} else {
				$user_name = $this->setup->defaultName;
			}

			$user_website = Misc::makeXSSsafe ($this->login->website);
			$name_class = 'hashover-name-plain';
			$is_twitter = false;

			// Check if user's name is a Twitter handle
			if ($user_name[0] === '@') {
				$user_name = mb_substr ($user_name, 1);
				$name_class = 'hashover-name-twitter';
				$is_twitter = true;
				$name_length = mb_strlen ($user_name);

				if ($name_length > 1 and $name_length <= 30) {
					if (empty ($user_website)) {
						$user_website = 'https://twitter.com/' . $user_name;
					}
				}
			}

			// Create element for logged user's name
			$main_form_column_spanner = new HTMLTag ('div', array (
				'class' => 'hashover-comment-name hashover-top-name'
			), false);

			// Check if user gave website
			if (!empty ($user_website)) {
				if ($is_twitter === false) {
					$name_class = 'hashover-name-website';
				}

				// Create link to user's website
				$main_form_hyperlink = new HTMLTag ('a', array (
					'href' => $user_website,
					'rel' => 'noopener noreferrer',
					'target' => '_blank',
					'title' => $user_name,
					'innerHTML' => $user_name
				), false);

				// Add username hyperlink to main form column spanner
				$main_form_column_spanner->appendChild ($main_form_hyperlink);
			} else {
				// No website
				$main_form_column_spanner->innerHTML ($user_name);
			}

			// Set classes user's name spanner
			$main_form_column_spanner->appendAttribute ('class', $name_class);

			// Add main form column spanner to inputs wrapper element
			$main_inputs->appendChild ($main_form_column_spanner);
		} else {
			// Logged out
			// Use default login inputs
			$main_inputs->appendInnerHTML ($this->defaultLoginInputs->innerHTML);
		}

		// Add inputs wrapper to form element
		$main_form->appendChild ($main_inputs);

		// Create fake "required fields" element as a SPAM trap
		$required_fields = new HTMLTag ('div', array (
			'id' => 'hashover-requiredFields'
		));

		$fake_fields = array (
			'summary' => 'text',
			'age' => 'hidden',
			'lastname' => 'text',
			'address' => 'text',
			'zip' => 'hidden',
		);

		// Create and append fake input elements to fake required fields
		foreach ($fake_fields as $name => $type) {
			$fake_input = new HTMLTag ('input', array (
				'type' => $type,
				'name' => $name,
				'value' => ''
			), false, true);

			// Add fake summary input element to fake required fields
			$required_fields->appendInnerHTML ($fake_input->asHTML ());
		}

		// Add fake input elements to form element
		$main_form->appendChild ($required_fields);

		// Post button locale
		$post_button = $this->locale->text['post-button'];

		// Create label element for comment textarea
		if ($this->setup->usesLabels === true) {
			$main_comment_label = new HTMLTag ('label', array (
				'for' => 'hashover-main-comment',
				'class' => 'hashover-comment-label',
				'innerHTML' => $post_button
			), false);

			// Add comment label to form element
			$main_form->appendChild ($main_comment_label);
		}

		// Get comment text if a comment cookie is set
		$comment_text = Misc::makeXSSsafe ($this->cookies->getValue ('comment'));

		// Comment form placeholder text
		$comment_form = $this->locale->text['comment-form'];

		// Create main textarea element and add it to form element
		$this->commentForm ($main_form, 'main', $comment_form, $comment_text);

		// Add page info fields to main form
		$this->pageInfoFields ($main_form);

		// Check if comment is a failed reply
		if ($this->cookies->getValue ('replied') !== null) {
			// If so, get the comment being replied to
			$replied = $this->cookies->getValue ('replied');

			// Create hidden reply to input element
			$reply_to_input = new HTMLTag ('input', array (
				'type' => 'hidden',
				'name' => 'reply-to',
				'value' => Misc::makeXSSsafe ($replied)
			), false, true);

			// And add hidden reply to input element to form element
			$main_form->appendChild ($reply_to_input);
		}

		// Create wrapper element for main form footer
		$main_form_footer = new HTMLTag ('div', array (
			'class' => 'hashover-form-footer'
		));

		// Create wrapper for form links
		$main_form_links_wrapper = new HTMLTag ('span', array (
			'class' => 'hashover-form-links'
		));

		// Add checkbox label element to main form buttons wrapper element
		if ($this->setup->fieldOptions['email'] !== false) {
			if ($this->login->userIsLoggedIn === false or !empty ($this->login->email)) {
				$subscribed = ($this->setup->subscribesUser === true);
				$subscribe_label = $this->subscribeLabel ('', 'main', $subscribed);
				$main_form_links_wrapper->appendChild ($subscribe_label);
			}
		}

		// Create and add accepted HTML revealer hyperlink
		if ($this->mode !== 'php') {
			$main_form_links_wrapper->appendChild ($this->acceptedFormatting ('main'));
		}

		// Add main form links wrapper to main form footer element
		$main_form_footer->appendChild ($main_form_links_wrapper);

		// Create wrapper for form buttons
		$main_form_buttons_wrapper = new HTMLTag ('span', array (
			'class' => 'hashover-form-buttons'
		));

		// Create "Login" / "Logout" button element
		if ($this->setup->allowsLogin !== false or $this->login->userIsLoggedIn === true) {
			$login_button = new HTMLTag ('input', array (
				'id' => 'hashover-login-button',
				'class' => 'hashover-submit',
				'type' => 'submit'
			), false, true);

			// Check login state
			if ($this->login->userIsLoggedIn === true) {
				// Logged in
				$login_button->appendAttribute ('class', 'hashover-logout');
				$logout_locale = $this->locale->text['logout'];

				// Create logged in attributes
				$login_button->createAttributes (array (
					'name' => 'logout',
					'value' => $logout_locale,
					'title' => $logout_locale
				));
			} else {
				// Logged out
				$login_button->appendAttribute ('class', 'hashover-login');

				// Create logged out attributes
				$login_button->createAttributes (array (
					'name' => 'login',
					'value' => $this->locale->text['login'],
					'title' => $this->locale->text['login-tip']
				));
			}

			// Add "Login" / "Logout" element to main form footer element
			$main_form_buttons_wrapper->appendChild ($login_button);
		}

		// Create "Post Comment" button element
		$main_post_button = new HTMLTag ('input', array (
			'id' => 'hashover-post-button',
			'class' => 'hashover-submit hashover-post-button',
			'type' => 'submit',
			'name' => 'post',
			'value' => $post_button,
			'title' => $post_button
		), false, true);

		// Add "Post Comment" element to main form buttons wrapper element
		$main_form_buttons_wrapper->appendChild ($main_post_button);

		// Add main form button wrapper to main form footer element
		$main_form_footer->appendChild ($main_form_buttons_wrapper);

		// Add main form footer to main form element
		$main_form->appendChild ($main_form_footer);

		// Add main form element to primary form wrapper
		$form_section->appendChild ($main_form);

		// Check if form position setting set to 'top'
		if ($this->setup->formPosition !== 'bottom') {
			// Add primary form wrapper to HashOver element
			$hashover_element->appendChild ($form_section);
		}

		if ($this->commentCounts['popular'] > 0) {
			// Create wrapper element for popular comments
			$popular_section = new HTMLTag ('div', array (
				'id' => 'hashover-popular-section'
			), false);

			// Hide popular comments wrapper if comments are to be initially hidden
			if ($this->mode !== 'php') {
				if ($this->setup->collapsesInterface === true or $this->setup->collapseLimit <= 0) {
					$popular_section->createAttribute ('style', 'display: none;');
				}
			}

			// Create wrapper element for popular comments title
			$pop_count_wrapper = new HTMLTag ('div', array (
				'class' => 'hashover-dashed-title'
			));

			// Create element for popular comments title
			$pop_count_element = new HTMLTag ('span', array (
				'class' => 'hashover-title'
			));

			// Add popular comments title text
			$popular_plural = ($this->commentCounts['popular'] !== 1) ? 1 : 0;
			$popular_comments_locale = $this->locale->text['popular-comments'];
			$pop_count_element->innerHTML ($popular_comments_locale[$popular_plural]);

			// Add popular comments title element to wrapper element
			$pop_count_wrapper->appendChild ($pop_count_element);

			// Add popular comments title wrapper element to popular comments section
			$popular_section->appendChild ($pop_count_wrapper);

			// Create element for popular comments to appear in
			$popular_comments = new HTMLTag ('div', array (
				'id' => 'hashover-top-comments'
			), false);

			// Add comments to HashOver element
			if (!empty ($this->popularComments)) {
				$popular_comments->innerHTML (trim ($this->popularComments));
			}

			// Add popular comments element to popular comments section
			$popular_section->appendChild ($popular_comments);

			// Add popular comments section to HashOver element
			$hashover_element->appendChild ($popular_section);
		}

		// Create wrapper element for comments
		$comments_section = new HTMLTag ('div', array (
			'id' => 'hashover-comments-section'
		), false);

		// Create wrapper element for comment count and sort dropdown menu
		$count_sort_wrapper = new HTMLTag ('div', array (
			'id' => 'hashover-count-wrapper',
			'class' => 'hashover-count-sort hashover-dashed-title'
		));

		// Create element for comment count
		$count_element = new HTMLTag ('span', array (
			'id' => 'hashover-count'
		));

		// Add comment count to comment count element
		if ($this->commentCounts['total'] > 1) {
			$count_element->innerHTML ($this->commentCounts['show-count']);
		}

		// Add comment count element to wrapper element
		$count_sort_wrapper->appendChild ($count_element);

		// JavaScript mode specific HTML
		if ($this->mode !== 'php') {
			// Hide wrapper if comments are to be initially hidden
			if ($this->setup->collapsesInterface === true) {
				$comments_section->createAttribute ('style', 'display: none;');
			}

			// Hide comment count if collapse limit is set at zero
			if ($this->setup->collapseLimit <= 0 or $this->commentCounts['total'] <= 1) {
				$count_sort_wrapper->createAttribute ('style', 'display: none;');
			}

			if ($this->commentCounts['total'] > 2) {
				// Create wrapper element for sort dropdown menu
				$sort_wrapper = new HTMLTag ('span', array (
					'id' => 'hashover-sort',
					'class' => 'hashover-select-wrapper'
				));

				// Create sort dropdown menu element
				$sort_select = new HTMLTag ('select', array (
					'id' => 'hashover-sort-select',
					'name' => 'sort',
					'size' => '1',
					'title' => $this->locale->text['sort']
				));

				// Array of select tag sort options
				$sort_options = array (
					'ascending'	=> $this->locale->text['sort-ascending'],
					'descending'	=> $this->locale->text['sort-descending'],
					'by-date'	=> $this->locale->text['sort-by-date'],
					'by-likes'	=> $this->locale->text['sort-by-likes'],
					'by-replies'	=> $this->locale->text['sort-by-replies'],
					'by-name'	=> $this->locale->text['sort-by-name']
				);

				// Run through each sort option
				foreach ($sort_options as $value => $html) {
					// Create option for sort dropdown menu element
					$option = new HTMLTag ('option', array (
						'value' => $value,
						'innerHTML' => $html
					), false);

					// Add sort option element to sort dropdown menu
					$sort_select->appendChild ($option);
				}

				// Create empty option group as spacer
				$spacer_optgroup = new HTMLTag ('optgroup', array (
					'label' => '&nbsp;'
				));

				// Add spacer option group to sort dropdown menu
				$sort_select->appendChild ($spacer_optgroup);

				// Create option group for threaded sort options
				$threaded_optgroup = new HTMLTag ('optgroup', array (
					'label' => $this->locale->text['sort-threads']
				));

				// Array of select tag threaded sort options
				$threaded_sort_options = array (
					'threaded-descending'	=> $this->locale->text['sort-descending'],
					'threaded-by-date'	=> $this->locale->text['sort-by-date'],
					'threaded-by-likes'	=> $this->locale->text['sort-by-likes'],
					'by-popularity'		=> $this->locale->text['sort-by-popularity'],
					'by-discussion'		=> $this->locale->text['sort-by-discussion'],
					'threaded-by-name'	=> $this->locale->text['sort-by-name']
				);

				// Run through each threaded sort option
				foreach ($threaded_sort_options as $value => $html) {
					// Create option for sort dropdown menu element
					$option = new HTMLTag ('option', array (
						'value' => $value,
						'innerHTML' => $html
					), false);

					// Add sort option element to threaded option group
					$threaded_optgroup->appendChild ($option);
				}

				// Add threaded sort options group to sort dropdown menu
				$sort_select->appendChild ($threaded_optgroup);

				// Add sort dropdown menu element to sort wrapper element
				$sort_wrapper->appendChild ($sort_select);

				// Add comment count element to wrapper element
				$count_sort_wrapper->appendChild ($sort_wrapper);
			}
		}

		// Add comment count and sort dropdown menu wrapper to comments section
		$comments_section->appendChild ($count_sort_wrapper);

		// Create element that will hold the comments
		$sort_div = new HTMLTag ('div', array (
			'id' => 'hashover-sort-section'
		), false);

		// Add comments to HashOver element
		if (!empty ($this->comments)) {
			$sort_div->innerHTML (trim ($this->comments));
		}

		// Add comments element to comments section
		$comments_section->appendChild ($sort_div);

		// Add comments element to HashOver element
		$hashover_element->appendChild ($comments_section);

		// Check if form position setting set to 'bottom'
		if ($this->setup->formPosition === 'bottom') {
			// Add primary form wrapper to HashOver element
			$hashover_element->appendChild ($form_section);
		}

		// Create end links wrapper element
		$end_links_wrapper = new HTMLTag ('div', array (
			'id' => 'hashover-end-links'
		));

		// Hide end links wrapper if comments are to be initially hidden
		if ($this->mode !== 'php' and $this->setup->collapsesInterface === true) {
			$end_links_wrapper->createAttribute ('style', 'display: none;');
		}

		// HashOver Comments hyperlink text
		$homepage_link_text = $this->locale->text['hashover-comments'];

		// Create link back to HashOver homepage (fixme! get a real page!)
		$homepage_link = new HTMLTag ('a', array (
			'href' => 'http://tildehash.com/?page=hashover',
			'id' => 'hashover-home-link',
			'target' => '_blank',
			'title' => $homepage_link_text,
			'innerHTML' => $homepage_link_text
		), false);

		// Add link back to HashOver homepage to end links wrapper element
		$end_links_wrapper->innerHTML ($homepage_link->asHTML () . ' &#8210;');

		// End links array
		$end_links = array ();

		if ($this->commentCounts['total'] > 1) {
			if ($this->setup->appendsRss === true
			    and $this->setup->apiStatus ('rss') !== 'disabled')
			{
				// Create RSS feed link
				$rss_link = new HTMLTag ('a', array (), false);
				$rss_link->createAttribute ('href', $this->setup->getHttpPath ('api/rss.php'));
				$rss_link->appendAttribute ('href', '?url=' . $this->safeURLEncode ($this->setup->pageURL), false);

				// "RSS Feed" locale string
				$rss_link_text = $this->locale->text['rss-feed'];

				// Create RSS feed attributes
				$rss_link->createAttributes (array (
					'id' => 'hashover-rss-link',
					'target' => '_blank',
					'title' => $rss_link_text,
					'innerHTML' => $rss_link_text
				));

				// Add RSS hyperlink to end links array
				$end_links[] = $rss_link->asHTML ();
			}
		}

		// Source Code hyperlink text
		$source_link_text = $this->locale->text['source-code'];

		// Create link to HashOver source code (fixme! can be done better)
		$source_link = new HTMLTag ('a', array (
			'href' => $this->setup->getBackendPath ('source-viewer.php'),
			'id' => 'hashover-source-link',
			'rel' => 'hashover-source',
			'target' => '_blank',
			'title' => $source_link_text,
			'innerHTML' => $source_link_text
		), false);

		// Add source code hyperlink to end links array
		$end_links[] = $source_link->asHTML ();

		if ($this->mode !== 'php') {
			// Create link to HashOver JavaScript source code
			$javascript_link = new HTMLTag ('a', array (
				'href' => $this->setup->getHttpPath ('comments.php'),
				'id' => 'hashover-javascript-link',
				'rel' => 'hashover-javascript',
				'target' => '_blank',
				'title' => 'JavaScript'
			), false);

			// Add JavaScript code hyperlink text
			$javascript_link->innerHTML ('JavaScript');

			// Add JavaScript hyperlink to end links array
			$end_links[] = $javascript_link->asHTML ();
		}

		// Add end links to end links wrapper element
		$end_links_wrapper->appendInnerHTML (implode (' &middot;' . PHP_EOL, $end_links));

		// Add end links wrapper element to HashOver element
		$hashover_element->appendChild ($end_links_wrapper);

		// Return all HTML with the HashOver wrapper element
		if ($hashover_wrapper === true) {
			return $hashover_element->asHTML ();
		}

		// Return just the HashOver wrapper element's innerHTML
		return $hashover_element->innerHTML;
	}
}
