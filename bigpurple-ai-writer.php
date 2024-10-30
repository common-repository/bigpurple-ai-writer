<?php

use BigPurpleAIWriter\ChatModal;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once 'class-ChatModal.php';

/**
 * Plugin Name: BigPurple AI Writer
 * Plugin URI: https://wordpress.org/plugins/bigpurple-ai-writer
 * Description: Streamline content creation with BigPurple AI Writer - access ChatGPT within your admin panel and copy-paste responses seamlessly. No more tab switching, just effortless productivity.
 * Version: 1.0.1
 * Author: bigpurple
 * License: GPL2
 */

class BigPurpleAIWriter {

	const PLUGIN_SLUG = 'bigpurple-ai-writer';

	/**
	 * This is the main entry point for the plugin.
	 *
	 * @return void
	 */
	public static function start() {
		self::init_admin_page();
		self::init_javascript();
		self::init_ajax();

		ChatModal::init();
	}

	private static function init_admin_page() {
		add_action(
			'admin_menu',
			function () {
				self::bigpurple_ai_writer_make_main_plugin_admin_page_and_settings();
			}
		);

		add_filter(
			'plugin_action_links_'.self::PLUGIN_SLUG.'/'.self::PLUGIN_SLUG.'.php',
			function($links) {
				return self::bigpurple_ai_writer_add_settings_link_to_plugins_page($links);
			},
		);

	}

	private static function init_javascript() {
		add_action(
			'admin_enqueue_scripts',
			function () {
				wp_register_script( 'bigpurple_ai_writer_script', '' );
				$javascript_code = self::bigpurple_ai_writer_render_javascript();
				wp_add_inline_script( 'bigpurple_ai_writer_script', $javascript_code );
				wp_enqueue_script( 'bigpurple_ai_writer_script' );
			}
		);
	}

	private static function init_ajax() {
		self::bigpurple_ai_writer_register_admin_ajax();
	}

	private static function bigpurple_ai_writer_add_settings_link_to_plugins_page($links) {

		$url = esc_url(add_query_arg(
			'page',
			self::PLUGIN_SLUG,
			get_admin_url() . 'options-general.php'
		));

		$settings_link = "<a href='$url'><strong>" . __('Settings') . '</strong></a>';

		array_push(
			$links,
			$settings_link
		);

		return $links;

	}

	private static function bigpurple_ai_writer_make_main_plugin_admin_page_and_settings() {
		add_submenu_page(
			'options-general.php',
			'ChatGPT',
			'BigPurple AI Writer',
			'manage_options',
			self::PLUGIN_SLUG,
			function () {

				echo '
                <style>
                    .plugin-intro {
                        width: 50%;
                    }
					.intro-image {
						max-width: 700px;
						width: 100%;
					}
                    .review-request {
                    background-color: #f9f9f9;
                    padding: 20px;
                    border-radius: 10px;
                    margin: 20px 0;
                    }
                    .review-request a {
                    color: #0073aa;
                    text-decoration: none;
                    }
                    .review-request a:hover {
                    color: #00a0d2;
                    }
                </style>';

				echo '<div class="wrap">';

				echo '<h1>BigPurple AI Writer</h1>';
				echo '<img class="intro-image" src="' . plugin_dir_url( __FILE__ ) . 'assets/how-to-open-bigpurple-ai-writer.png' . '" alt="How to Open BigPurple AI Writer">';

				echo '
				<div class="plugin-intro">

					<h2>Welcome to BigPurple AI Writer!</h2>

					<div class="description">
					
						<p>This plugin is free, but you do need an <a href="https://platform.openai.com/signup" target="_blank">OpenAI account and API key</a> to use it.</p>

						<p>
						Enhance your content creation experience like never before with BigPurple AI Writer! Seamlessly integrated into your WordPress admin panel, this useful plugin allows you to harness the power of ChatGPT without ever leaving your site.
						</p>

						<p>
						Say goodbye to tab switching and streamline your workflow by accessing ChatGPT through a convenient link in the site\'s top bar. With just a click, you can open a dialog and interact directly with ChatGPT, generating valuable and insightful responses in real-time.
						</p>

						<p>
						Our user-friendly interface makes it a breeze to copy and paste the generated responses into your content, eliminating the hassle of juggling multiple browser tabs. Whether you need assistance with article ideas, creative writing, or research insights, BigPurple AI Writer is your reliable companion, always ready to provide valuable input.
						</p>

						<p>
						Image generation is coming soon!
						</p>

						</div>

					<div class="review-request">
						<p>If you find our plugin useful, kindly leave a positive review on the WordPress plugin page. Your feedback not only benefits us, but also helps other WordPress users discover this innovative tool.</p>
					
						<p>Thank you for your support! Click <a href="https://wordpress.org/support/plugin/bigpurple-ai-writer/reviews/#new-post" target="_blank">here</a> to leave your review.</p>
					</div>
				</div>';

				echo '</div>';
				self::bigpurple_ai_writer_display_settings_form();
			}
		);

		self::bigpurple_ai_writer_register_settings();
	}

	private static function bigpurple_ai_writer_register_settings() {
		$setting_slug = 'openai_api_key';

		add_settings_section(
			self::PLUGIN_SLUG . '-settings',
			'General Settings',
			function () {
			},
			self::PLUGIN_SLUG
		);

		register_setting( self::PLUGIN_SLUG, self::PLUGIN_SLUG . '_' . $setting_slug );

		add_settings_field(
			self::PLUGIN_SLUG . '_' . $setting_slug,
			'OpenAI API Key',
			function () use ( $setting_slug ) {
				$option_value = get_option( self::PLUGIN_SLUG . '_' . $setting_slug );
				echo '<input type="password" name="' . esc_attr( self::PLUGIN_SLUG . '_' . $setting_slug ) . '" value="' . esc_attr( $option_value ) . '" />';
				echo '<p class="description">Put your OpenAI API key here. You can find your key at <a href="https://platform.openai.com/account/api-keys" target="_blank">https://platform.openai.com/account/api-keys</a>.</p>';
			},
			self::PLUGIN_SLUG,
			self::PLUGIN_SLUG . '-settings'
		);
	}


	private static function bigpurple_ai_writer_display_settings_form() {
		echo '<h1>Settings</h1>';
		echo '<form method="post" action="options.php">';
		settings_fields( self::PLUGIN_SLUG );
		do_settings_sections( self::PLUGIN_SLUG );
		submit_button();
		echo '</form>';
	}

	/**
	 * Gets a setting value.
	 *
	 * @return mixed
	 */
	public static function bigpurple_ai_writer_get_openai_api_key() {
		return get_option( self::PLUGIN_SLUG . '_' . 'openai_api_key' );
	}

	/**
	 * Recursively sanitizes an array or string using WordPress specific sanitization functions.
	 *
	 * @param mixed $data The data to sanitize.
	 * @return mixed The sanitized data.
	 */
	public static function sanitize_recursive( $data ) {
		if ( is_array( $data ) ) {
			foreach ( $data as $key => $value ) {
				if ( is_array( $value ) ) {
					$data[ $key ] = self::sanitize_recursive( $value );
				} else {
					$data[ $key ] = sanitize_text_field( $value );
				}
			}
		} else {
			$data = sanitize_text_field( $data );
		}

		return $data;
	}

	/**
	 * Registers ajax endpoints.
	 *
	 * TODO this currently assumes that everyon wants the AI endpoint
	 *
	 * @return void
	 */
	public static function bigpurple_ai_writer_register_admin_ajax() {
		add_action(
			'wp_ajax_soflyy_toolkit',
			function () {
				check_ajax_referer( 'soflyy_toolkit', 'ajax_nonce' );
				if (!current_user_can('manage_options')) {
					// If the current user doesn't have the manage_options capability, return an error.
					echo wp_json_encode(array('error' => esc_html('Unauthorized access')));
					wp_die();
				}
            // phpcs:ignore
            $prompt = isset($_POST['prompt']) ? self::sanitize_recursive($_POST['prompt']) : '';
				$command    = sanitize_text_field( wp_unslash( $_POST['command'] ?? '' ) );
				$api_key    = (string) self::bigpurple_ai_writer_get_openai_api_key();
				if ( $command === 'chat' ) {
					if ( is_string( $prompt ) ) {
						$prompt = array(
							array(
								'role'    => 'system',
								'content' => 'You are a helpful assistant.',
							),
							array(
								'role'    => 'user',
								'content' => $prompt,
							),
						);
					}

					$response = self::openai_chat( $api_key, 'gpt-3.5-turbo', $prompt );

					if ( isset( $response['error'] ) && $response['error'] ) {
						echo wp_json_encode( array( 'error' => esc_html($response['error']['message']) ) );
						wp_die();
					}

					$lastChatResponse = $response['choices'][0]['message']['content'];

					$response = array( 'data' => $lastChatResponse );
					echo wp_json_encode( esc_html($response) );
					wp_die();
				}
			}
		);
	}


	public static function openai_chat( $api_key, $model, $messages ) {
		$headers = array(
			'Content-Type'  => 'application/json',
			'Authorization' => 'Bearer ' . $api_key,
		);

		$data = array(
			'model'    => $model,
			'messages' => $messages,
		);

		$response = wp_remote_post(
			'https://api.openai.com/v1/chat/completions',
			array(
				'method'      => 'POST',
				'headers'     => $headers,
				'body'        => wp_json_encode( $data ),
				'data_format' => 'body',
				'timeout'     => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			return array( 'error' => $error_message );
		} else {
			return json_decode( wp_remote_retrieve_body( $response ), true );
		}
	}


	public static function bigpurple_ai_writer_render_javascript() {
		ob_start();
		/**
		 * Example usage of the SoflyyAiToolkit class:
		 *
		 * Let's imagine that we want to use the SoflyyAiToolkit class in order to send a chat command, generate an image,
		 * and save that image to the media library. We would do so with the following steps:
		 *
		 * 1. Send a chat command
		 *    We first need to prepare the plugin slug and the prompt. The plugin slug could be a string such as "my-plugin-slug",
		 *    and the prompt could be a string or an array of objects that represent chat messages.
		 *
		 *    Example:
		 *    const pluginSlug = "my-plugin-slug";
		 *    const chatPrompt = "Hello, what can you do?";
		 *
		 *    window.SoflyyAiToolkit.chat(pluginSlug, chatPrompt)
		 *        .then(response => console.log(response))
		 *        .catch(error => console.error(error));
		 *
		 *    This will send a chat command and log the response.
		 *
		 * All these methods are asynchronous, which means they return Promises. This allows us to handle the response data
		 * and errors appropriately, using the .then() and .catch() methods of the returned Promises.
		 */
		?>
		<script>
			/**
			 * This immediately-invoked function expression (IIFE) encapsulates the SoflyyAiToolkit
			 * class definition and its assignment to the window object, preventing the class itself
			 * from being accessible in the global scope.
			 */
			(function() {
				/**
				 * SoflyyAiToolkit is a class to perform different operations using AJAX calls.
				 * @constructor
				 */
				class SoflyyAiToolkit {
					/**
					 * In the constructor, we initialize the ajax_object with the global soflyy_toolkit_ajax_object,
					 * and we also set the action to be 'soflyy_toolkit', which will be used in all AJAX calls.
					 */
					constructor() {
						this.action = 'soflyy_toolkit';
					}

					/**
					 * Perform an AJAX call using jQuery.
					 * @param {string} command - The command to be sent as part of the AJAX call.
					 * @param {Object} additionalData - Any additional data to be sent as part of the AJAX call.
					 * @returns {Promise<Object>|null} - A promise that resolves to the response data, or null if there was an error.
					 */
					async performAjaxCall(command, additionalData = {}) {
						try {
							const response = await jQuery.ajax({
								url: <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>,
								type: 'POST',
								data: {
									action: this.action,
									ajax_nonce: <?php echo wp_json_encode( wp_create_nonce( 'soflyy_toolkit' ) ); ?>,
									command: command,
									...additionalData
								},
							});

							const parsedResponse = JSON.parse(response);

							if (parsedResponse.error) {
								alert(parsedResponse.error);
								return null;
							}

							return parsedResponse.data;

						} catch (error) {
							console.error(error);
							return null;
						}
					}

					/**
					 * Send a chat command with a given plugin slug and prompt.
					 * @param {string} pluginSlug - The slug of the plugin.
					 * @param {string|Array} prompt - A string prompt or an array of chat messages.
					 * @returns {Promise<Object>|null} - A promise that resolves to the response data, or null if there was an error.
					 */
					async chat(pluginSlug, prompt) {
						return await this.performAjaxCall('chat', {
							prompt: prompt,
							plugin_slug: pluginSlug
						});
					}
				}

				/**
				 * An instance of the SoflyyAiToolkit class is assigned to the window.SoflyyAiToolkit variable,
				 * making it available for use in other scripts.
				 */
				window.SoflyyAiToolkit = new SoflyyAiToolkit();
			})();
		</script>
		<?php
		$javascript_as_string = ob_get_clean();
		return $javascript_as_string;
	}
}

BigPurpleAIWriter::start();
