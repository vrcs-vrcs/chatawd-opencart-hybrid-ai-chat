<?php
$_['heading_title'] = '<font color="blue">ChatVRCS Settings</font>';
$_['heading_title2'] = 'ChatVRCS Settings';

$_['text_extension'] = 'Extensions';
$_['text_edit'] = 'Edit Settings';
$_['entry_status'] = 'Status';

$_['entry_api_key'] = 'OpenAI API Key';
$_['entry_model'] = 'Model (e.g., gpt-3.5-turbo)';
$_['entry_temperature'] = 'Randomness (0.0 – 2.0)';
$_['entry_prompt'] = 'Default Prompt';
$_['entry_color'] = 'Chat Color';
$_['entry_history_limit'] = 'History Limit';
$_['text_prompt_default'] = 'Respond as if you are a store assistant.';
$_['entry_get_api_key']     = 'ChatVRCS API key request process:';
$_['entry_select_pack']     = 'Choose a package';
$_['entry_select_pack_help']     = 'Package details';
$_['entry_reward_presets'] = 'Reward Point Presets';
$_['text_reward_help']     = 'Enter the selectable point values separated by commas (e.g., 5,10,25,50). If left empty, the reward point feature will not appear in the chat.';

$_['text_success'] = 'Settings saved successfully!';
$_['error_permission'] = 'You do not have permission to modify this module!';

// New texts for registration
$_['entry_registration_id'] = 'Registration ID (vrcs.hu)';
$_['help_registration_id'] = 'This ID is provided by vrcs.hu and is used to identify your store. If you don’t have one yet, click the Register button.';
$_['button_register'] = 'Register';
$_['text_error_server'] = 'An error occurred while communicating with the server. Please try again later.';
$_['error_registry'] = 'Failed to register at vrcs.hu. HTTP Code: %s Error: %s';

$_['text_free_pack'] = 'Basic Package (Free)';
$_['text_free_pack_descr'] = 'Description: Current functionality (basic AI chat version, one-time context memory during conversation).';

$_['text_basic_pack'] = 'Remembers Last 3 Chats (5,000 HUF/year)';
$_['text_basic_pack_descr'] = 'The AI remembers the last 3 chats, enabling more personalized responses.';

$_['text_standard_pack'] = 'Remembers Last 6 Chats (7,000 HUF/year)';
$_['text_standard_pack_descr'] = 'The AI remembers the last 6 chats, enabling more personalized responses.';

$_['text_premium_pack'] = 'Operator Takeover and Return (15,000 HUF/year)';
$_['text_premium_pack_descr'] = 'The store owner can take over the chat from the AI, respond manually, then hand it back when unavailable. Includes access to the admin interface.';

$_['text_vrcs_key'] = 'Use the VRCS Convenience Key';
$_['text_chataiwd_key'] = 'Register My Own ChatVRCS API Key';
$_['text_chataiwd_kredit'] = 'Purchase ChatVRCS API usage credits';
$_['text_kredit_egyenleg'] = 'My credit balance:';
$_['text_billing'] = 'Next billing when balance drops below $1. ($10) ';

$_['text_confirm_package_switch'] = 'Are you sure you want to switch to the %s package?';
$_['entry_dispatcher'] = 'Dispatcher interface:';
$_['help_dispatcher'] = 'Dispatcher interface:';
$_['button_dispatcher'] = 'Dispatcher interface';
$_['button_fizetés'] = 'Payment';
$_['button_fizetes'] = 'Payment';

// New entries for the added fields
$_['entry_chat_button'] = 'Chat Button Text';
$_['help_chat_button'] = 'Enter the text to display on the chat button to encourage users to start a conversation.';
$_['placeholder_chat_button'] = 'e.g.: ChatVRCS Ask Now!';

$_['entry_ai_response_header'] = 'AI Response Header';
$_['help_ai_response_header'] = 'Enter the header text to display when the AI responds in the chat.';
$_['placeholder_ai_response_header'] = 'e.g.: AI Response';

$_['entry_dispatcher_response_header'] = 'Dispatcher Response Header';
$_['help_dispatcher_response_header'] = 'Enter the header text to display when the dispatcher responds in the chat.';
$_['placeholder_dispatcher_response_header'] = 'e.g.: Dispatcher Response';

$_['entry_ai_response_indicator'] = 'AI Response Indicator';
$_['help_ai_response_indicator'] = 'Enter the text that appears next to the chat icon when the AI responds.';
$_['placeholder_ai_response_indicator'] = 'e.g.: AI is replying';

$_['entry_dispatcher_response_indicator'] = 'Dispatcher Response Indicator';
$_['help_dispatcher_response_indicator'] = 'Enter the text that appears next to the chat icon when the dispatcher responds.';
$_['placeholder_dispatcher_response_indicator'] = 'e.g.: Dispatcher is replying';

$_['entry_welcome_message'] = 'Welcome Message';
$_['help_welcome_message'] = 'Enter the welcome message to display when the chat is initiated.';
$_['placeholder_welcome_message'] = 'e.g.: Hello! How can I help you today?';



$_['help_packages'] = '
<b>Basic Package (Free)</b><br>
Description: Current functionality (basic AI chat version, one-time context memory during conversation).<br><br>

<b>Remembers Last 3 Chats (5,000 HUF/year)</b><br>
The AI remembers the last 3 chats, enabling more personalized responses.<br><br>

<b>Remembers Last 6 Chats (7,000 HUF/year)</b><br>
The AI remembers the last 6 chats, enabling more personalized responses.<br><br>

<b>Operator Takeover and Return (15,000 HUF/year)</b><br>
The store owner can take over the chat from the AI, respond manually, then hand it back when unavailable. Includes access to the admin interface.<br><br>
';


// Tooltips
$_['help_api_key'] = 'Enter your ChatGPT API key obtained from OpenAI. It is required for communication.';
$_['help_model'] = 'Choose the ChatGPT model you wish to use. Different models vary in price and capabilities. In most cases, gpt-3.5-turbo is a good balance between cost and performance.';
$_['help_temperature'] = "Controls the creativity of responses:\n0.0 – Fully predictable and accurate, no creativity.\n0.7 (default) – Balanced creativity and accuracy.\n1.0+ – More creative and varied responses, possibly less accurate.\n2.0 – Maximum creativity, very diverse responses, potentially too free.";
$_['help_prompt'] = 'Provide a default prompt to guide the AI responses. Example: "You are a helpful store assistant answering customer questions about products."';
$_['help_placeholder'] = 'Type something you want to send to the AI. E.g., Respond as if you are a store assistant.';
$_['help_color'] = 'Select the primary color for the chat window (header, buttons, etc.).';
$_['help_history_limit'] = 'How many previous question-answer pairs to send to ChatGPT (0–5). 0 means no history is sent. Higher values provide more context but use more tokens.';
$_['help_select_pack'] = 'help_select_pack';
$_['help_vrcs_source'] = 'Uses the API key provided by VRCS.HU, payment is handled through us. No ChatGPT registration required.';
$_['help_chataiwd_source'] = 'You need to provide your own ChatGPT API key, which can be obtained from OpenAI.';
$_['help_kredit_vrcs'] = '<br><b>ChatAWD Hybrid Infrastructure and Usage Credits</b><br><br>
ChatAWD is a premium, high-performance hybrid sales and customer support engine. The system combines OpenAI’s most advanced language models (GPT-4o), fine-tuned local vector search, and user preference tracking with an intelligent dispatcher dashboard (featuring real-time cart reconstruction, purchase alerts, coupons, targeted loyalty point promises, and a wheel of fortune).<br><br>
Maintaining this complex cloud infrastructure—including AI computational capacities, secure API routes, and real-time database synchronization between stores—operates on a utility-based credit system.<br><br>
Instead of expensive and rigid flat-rate monthly subscriptions, ChatAWD applies a completely transparent, usage-based (pay-as-you-go) settlement. You only pay for the AI and dispatcher transactions actually generated by your store.<br><br>
<b>To ensure you can experience the module’s conversion-boosting effect completely risk-free, we provide a 2 USD starting credit upon registration.</b><br><br>';





$_['help_get_api_key'] = '🔑 How to get a ChatGPT API key?<br>Open the following website:<br><a href="https://platform.openai.com/account/api-keys" target="_blank">https://platform.openai.com/account/api-keys</a><br><br>Log in or sign up for an account on the OpenAI platform (you can also use a Google account).<br><br>After logging in, click the “Create new secret key” button.<br><br>Copy the generated API key and paste it into this module.<br><br>Important: You can view the full API key only once!<br><br>If you lose it, generate a new one on the same page.
<br><br>
💰 Check your API usage balance here:<br><br>
<a href="https://platform.openai.com/settings/organization/billing/overview" target="_blank">Billing Overview</a><br><br>
<a href="https://platform.openai.com/usage" target="_blank">Usage Statistics</a><br><br>
';

// Model descriptions
$_['gpt-4.1-nano'] = ' – Small, fast response time, low cost – great for simple tasks.';
$_['gpt-4'] = ' – More powerful, but may be slower and more expensive.';
$_['gpt-4.1-mini'] = ' – Balanced choice: speed and quality.';
$_['gpt-4-turbo'] = ' – Optimal choice: fast, cheaper, and powerful. Typically the best value-for-money model.';
$_['gpt-3.5-turbo'] = ' – Even cheaper, less accurate, but sufficient for many tasks.';
$_['gpt-3.5-turbo-16k'] = ' – Larger context window – suitable for longer conversations or references.';
$_['gpt-4O'] = ' – The latest real-time model, very fast and powerful. Use this if available.';
$_['gpt-4O-mini'] = ' – For light tasks and quick replies – e.g., auto responses, searches.';
$_['gpt-4.1'] = ' – Next-gen GPT model, may be preview or special edition (depending on API version).';

$_['entry_duration'] = 'Subscription period:';
$_['text_duration_3'] = '3 months';
$_['text_duration_6'] = '6 months';
$_['text_duration_9'] = '9 months';
$_['text_duration_12'] = '12 months';
$_['text_duration_cancel'] = 'Cancel';

$_['help_recovery_info'] = 'Set up a maximum of 5 reminder emails. Empty rows will not be sent.';
$_['entry_recovery_delay'] = 'Delay';
$_['entry_recovery_subject'] = 'Email subject (Empty = not sent)';
$_['entry_recovery_content'] = 'Message content';
$_['text_hours'] = 'Hour';
$_['text_days'] = 'Day';
$_['text_subject_placeholder'] = 'E.g.: Did you forget something?';
$_['text_content_placeholder'] = 'Message...';

$_['text_ai_syncr'] = 'AI Synchronization';
$_['text_syncrestart'] = 'Restart Synchronization';
$_['text_syncrestart_click'] = 'Click here if you want to re-synchronize the database content with the AI.';



// Constructor fallback values
$_['text_chat_button_fallback']                   = 'ChatGpt Ask Now!';
$_['text_ai_response_header_fallback']            = 'AI Response';
$_['text_dispatcher_response_header_fallback']    = 'Dispatcher Response';
$_['text_ai_response_indicator_fallback']         = 'AI is responding...';
$_['text_dispatcher_response_indicator_fallback'] = 'Dispatcher is responding...';
$_['text_welcome_message_fallback']               = 'Hi! How can I help you today?';

// AJAX / API error messages
$_['error_permission']                            = 'Warning: You do not have permission to modify this module!';
$_['error_already_registered']                    = 'Already registered';
$_['error_domain_not_found']                      = 'The store domain name could not be determined.';
$_['error_invalid_input']                         = 'Please provide your email address!';
$_['error_registry']                              = 'Registration failed! HTTP Code: %s, Error: %s';
$_['error_invalid_server_response']               = 'Invalid response from authorization server';
$_['error_no_registration']                       = 'No valid registration ID found!';
$_['error_server_communication']                  = 'Error during communication with the remote server.';
$_['error_invalid_field']                         = 'Invalid field name!';
$_['error_unknown']                               = 'Unknown error based on server response.';

// Successful registration
$_['text_register_success']                       = 'Successful registration!';

$_['text_be_patient_registration']          = 'Please be patient, registration is in progress...';
$_['text_failed']                            = 'Fault';
$_['text_choose_package']                   = 'Please choose a package';
$_['text_restart_sync']                     = 'Are you sure you want to restart the full AI sync? This may take a while.';
$_['text_intitializing']                     = 'Initializing AI module...';
$_['error_schema']                     = 'Error: The schema was not received properly.';
$_['text_ai_learning_help'] = 'Please do not close the window. <br>The AI id currently analyzing and indexing the web store is offerings so that the chat module and intelligent dispatcher search engine can provide precise answers and product recommendations.';
$_['tab_general'] = 'General';
$_['tab_chat_settings'] = 'Chat Settings';
$_['tab_faq'] = 'FAQ';
$_['tab_tools'] = 'Features';
$_['tab_abandoned'] = 'Abandoned cart';
$_['entry_faq_icon_type'] = 'Icon type';
$_['entry_faq_visual_icon'] = 'Icon';
$_['entry_faq_visual_image'] = 'Image';
$_['entry_faq_question'] = 'Question';
$_['entry_faq_answer'] = 'Answer';
$_['help_tools_info'] = 'Here you can turn on or off the extra features that appear in the chat window..';
$_['help_tools_info'] = 'Allows users to notify the dispatcher.';
$_['help_tool_voice'] = 'Show microphone icon for voice input.';
$_['help_tool_image'] = 'Allows users to send images to the AI for dispatcher.';
$_['help_tool_email'] = 'Displays the mail icon for direct contact.';
$_['help_tool_faq'] = 'Turns the display of FAQs completely off or on.';
$_['help_tool_whatsapp'] = 'Show WhatsApp direct message button.';
$_['help_whatsapp_number'] = 'Enter the phone number in international format.';
$_['text_time'] = 'Time...';
$_['text_subject'] = 'E.g.: Did you forget something?';
$_['error_initializing_ai'] = 'Error initializing AI';
$_['text_ai_prepared'] = 'AI successfully prepared! Starting system...';
$_['text_ai_learning'] = 'AI learning process:';
$_['error_ai_arming'] = 'An error occurred while arming the AI:';
$_['error_network'] = 'Network error during shutdown. Retrying in 5 seconds...';
$_['error_closing'] = 'Error during closing:';
$_['text_optimizing'] = 'Optimizing and training AI models... Please wait.';
$_['text_empty'] = 'Empty';
$_['text_error'] = 'Error';
$_['text_unknown_error'] = 'Unknown error';
$_['error_save'] = 'Error during saving:';
$_['text_registration'] = 'Registration';
$_['entry_tool_bell']                             = 'Call Dispatcher (Bell)';
$_['help_tool_bell']                              = 'Allows users to notify the dispatcher.';
$_['entry_tool_voice']                            = 'Voice Recognition';
$_['entry_tool_image']                            = 'Image Upload';
$_['entry_tool_emoji']                            = 'Emoji Picker';
$_['entry_tool_email']                            = 'Email Contact Form';
$_['entry_tool_faq']                              = 'FAQ Module';
$_['entry_tool_whatsapp']                         = 'WhatsApp Contact';
$_['entry_whatsapp_placeholder']                  = 'e.g.: +36 70 123 4567';
$_['button_save']                                 = 'Save';
$_['button_back']                                 = 'Back';
$_['button_add']                                  = 'Add';
$_['button_remove']                               = 'Remove';
$_['text_enabled']                                = 'Enabled';
$_['text_disabled']                               = 'Disabled';