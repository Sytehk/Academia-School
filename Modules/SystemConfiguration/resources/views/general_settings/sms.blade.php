  <div id="sms" class="tab-pane fade">
      <div class="card card-default border p-5 my-2">
          <div class="card-header">
              <span class="card-title">{{ _lang('SMS Settings') }}</span>
          </div>
          <div class="">
              <form method="post" action="{{ url('administration/general_settings/update') }}"
                  enctype="multipart/form-data">
                  @csrf

                  <div class="row">
                      <div class="col-lg-6">
                          <div class="form-group">
                              <label for="sms_gateway" class="control-label my-2">{{ _lang('SMS Gateway') }}</label>
                              <select id="sms_gateway" name="sms_gateway" class="form-select">
                                  <option value="twilio" {{ get_option('sms_gateway') == 'twilio' ? 'selected' : '' }}>
                                      Twilio</option>
                                  <option value="bulksmsbd"
                                      {{ in_array(get_option('sms_gateway'), ['bulksmsbd', 'bulkSMSBD'], true) ? 'selected' : '' }}>
                                      Bulk SMS BD</option>
                                  <option value="mimsms" {{ get_option('sms_gateway') == 'mimsms' ? 'selected' : '' }}>
                                      MimSMS</option>
                                  <option value="infobip" {{ get_option('sms_gateway') == 'infobip' ? 'selected' : '' }}>
                                      Infobip</option>
                                  <option value="clickatell" {{ get_option('sms_gateway') == 'clickatell' ? 'selected' : '' }}>
                                      Clickatell</option>
                                  <option value="messagebird" {{ get_option('sms_gateway') == 'messagebird' ? 'selected' : '' }}>
                                      MessageBird</option>
                              </select>
                          </div>
                      </div>

                      <div class="col-lg-6">
                          <div class="form-group">
                              <label for="sms_test_mode" class="control-label my-2">{{ _lang('SMS Test Mode') }}</label>
                              <select id="sms_test_mode" name="sms_test_mode" class="form-select">
                                  <option value="true" {{ get_option('sms_test_mode') == 'true' ? 'selected' : '' }}>
                                      True
                                  </option>
                                  <option value="false"
                                      {{ get_option('sms_test_mode') == 'false' ? 'selected' : '' }}>False
                                  </option>
                              </select>
                          </div>
                      </div>
                  </div>

                  <div class="row gateway-fields" data-gateway="bulksmsbd">
                      <div class="col-12">
                          <h5 class="mt-4 mb-3">{{ _lang('Bulk SMS BD') }}</h5>
                      </div>
                      <div class="col-lg-6">
                          <div class="form-group">
                              <label for="bulk_sms_api_key" class="control-label my-2">{{ _lang('API Key') }}</label>
                              <input type="text" id="bulk_sms_api_key" name="bulk_sms_api_key" class="form-control"
                                  value="{{ get_option('bulk_sms_api_key') }}">
                          </div>
                      </div>
                      <div class="col-lg-6">
                          <div class="form-group">
                              <label for="bulk_sms_sender_id" class="control-label my-2">{{ _lang('Sender ID') }}</label>
                              <input type="text" id="bulk_sms_sender_id" name="bulk_sms_sender_id" class="form-control"
                                  value="{{ get_option('bulk_sms_sender_id') }}">
                          </div>
                      </div>
                  </div>

                  <div class="row gateway-fields" data-gateway="mimsms">
                      <div class="col-12">
                          <h5 class="mt-4 mb-3">{{ _lang('MimSMS') }}</h5>
                      </div>
                      <div class="col-lg-4">
                          <div class="form-group">
                              <label for="mimsms_username" class="control-label my-2">{{ _lang('User Name') }}</label>
                              <input type="text" id="mimsms_username" name="mimsms_username" class="form-control"
                                  value="{{ get_option('mimsms_username') }}" placeholder="your@email.com">
                          </div>
                      </div>
                      <div class="col-lg-4">
                          <div class="form-group">
                              <label for="mimsms_api_key" class="control-label my-2">{{ _lang('API Key') }}</label>
                              <input type="text" id="mimsms_api_key" name="mimsms_api_key" class="form-control"
                                  value="{{ get_option('mimsms_api_key') }}">
                          </div>
                      </div>
                      <div class="col-lg-4">
                          <div class="form-group">
                              <label for="mimsms_sender_name" class="control-label my-2">{{ _lang('Sender Name') }}</label>
                              <input type="text" id="mimsms_sender_name" name="mimsms_sender_name" class="form-control"
                                  value="{{ get_option('mimsms_sender_name') }}">
                          </div>
                      </div>
                  </div>

                  <div class="row gateway-fields" data-gateway="twilio">
                      <div class="col-12">
                          <h5 class="mt-4 mb-3">{{ _lang('Twilio') }}</h5>
                      </div>
                      <div class="col-lg-4">
                          <div class="form-group">
                              <label for="twilio_sid" class="control-label my-2">{{ _lang('Twilio SID') }}</label>
                              <input type="text" id="twilio_sid" name="twilio_sid" class="form-control"
                                  value="{{ get_option('twilio_sid') }}">
                          </div>
                      </div>
                      <div class="col-lg-4">
                          <div class="form-group">
                              <label for="twilio_token" class="control-label my-2">{{ _lang('Twilio Token') }}</label>
                              <input type="text" id="twilio_token" name="twilio_token" class="form-control"
                                  value="{{ get_option('twilio_token') }}">
                          </div>
                      </div>
                      <div class="col-lg-4">
                          <div class="form-group">
                              <label for="twilio_from_number" class="control-label my-2">{{ _lang('From Number') }}</label>
                              <input type="text" id="twilio_from_number" name="twilio_from_number" class="form-control"
                                  value="{{ get_option('twilio_from_number') }}">
                          </div>
                      </div>
                  </div>

                  <div class="row gateway-fields" data-gateway="infobip">
                      <div class="col-12">
                          <h5 class="mt-4 mb-3">{{ _lang('Infobip') }}</h5>
                      </div>
                      <div class="col-lg-4">
                          <div class="form-group">
                              <label for="infobip_base_url" class="control-label my-2">{{ _lang('Base URL') }}</label>
                              <input type="text" id="infobip_base_url" name="infobip_base_url" class="form-control"
                                  value="{{ get_option('infobip_base_url') }}" placeholder="https://xxxxx.api.infobip.com">
                          </div>
                      </div>
                      <div class="col-lg-4">
                          <div class="form-group">
                              <label for="infobip_api_key" class="control-label my-2">{{ _lang('API Key') }}</label>
                              <input type="text" id="infobip_api_key" name="infobip_api_key" class="form-control"
                                  value="{{ get_option('infobip_api_key') }}">
                          </div>
                      </div>
                      <div class="col-lg-4">
                          <div class="form-group">
                              <label for="infobip_sender_id" class="control-label my-2">{{ _lang('Sender ID') }}</label>
                              <input type="text" id="infobip_sender_id" name="infobip_sender_id" class="form-control"
                                  value="{{ get_option('infobip_sender_id') }}">
                          </div>
                      </div>
                  </div>

                  <div class="row gateway-fields" data-gateway="clickatell">
                      <div class="col-12">
                          <h5 class="mt-4 mb-3">{{ _lang('Clickatell') }}</h5>
                      </div>
                      <div class="col-lg-6">
                          <div class="form-group">
                              <label for="clickatell_api_key" class="control-label my-2">{{ _lang('API Key') }}</label>
                              <input type="text" id="clickatell_api_key" name="clickatell_api_key" class="form-control"
                                  value="{{ get_option('clickatell_api_key') }}">
                          </div>
                      </div>
                  </div>

                  <div class="row gateway-fields" data-gateway="messagebird">
                      <div class="col-12">
                          <h5 class="mt-4 mb-3">{{ _lang('MessageBird') }}</h5>
                      </div>
                      <div class="col-lg-6">
                          <div class="form-group">
                              <label for="messagebird_api_key" class="control-label my-2">{{ _lang('API Key') }}</label>
                              <input type="text" id="messagebird_api_key" name="messagebird_api_key" class="form-control"
                                  value="{{ get_option('messagebird_api_key') }}">
                          </div>
                      </div>
                      <div class="col-lg-6">
                          <div class="form-group">
                              <label for="messagebird_originator" class="control-label my-2">{{ _lang('Originator') }}</label>
                              <input type="text" id="messagebird_originator" name="messagebird_originator" class="form-control"
                                  value="{{ get_option('messagebird_originator') }}" placeholder="MSchool">
                          </div>
                      </div>
                  </div>

                  <div class="row">
                      <div class="form-group">
                          <div class="col-lg-12">
                              <button type="submit" class="btn btn-primary mt-5">{{ _lang('Save Settings') }}</button>
                          </div>
                      </div>
                  </div>
              </form>
          </div>
      </div>
  </div>
