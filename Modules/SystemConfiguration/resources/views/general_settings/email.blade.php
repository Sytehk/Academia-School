  <div id="email" class="tab-pane fade">
      <div class="card card-default border p-5 my-2">
          <div class="card-header">
              <span class="card-title">{{ _lang('Email Settings') }}</span>
          </div>
          <div class="">
              <form method="post" action="{{ url('administration/general_settings/update') }}"
                  enctype="multipart/form-data">
                  @csrf
                  <div class="row">
                      <div class="col-lg-6">
                          <div class="form-group">
                              <label class="control-label my-2">{{ _lang('Mail Type') }}</label>
                              <select class="form-select" name="mail_type" id="mail_type">
                                  <option value="mail" {{ get_option('mail_type') == 'mail' ? 'selected' : '' }}>
                                      {{ _lang('PHP Mail') }}</option>
                                  <option value="smtp" {{ get_option('mail_type') == 'smtp' ? 'selected' : '' }}>
                                      {{ _lang('SMTP') }}</option>
                              </select>
                          </div>
                      </div>

                      <div class="col-lg-6">
                          <div class="form-group">
                              <label class="control-label my-2">{{ _lang('From Email') }}</label>
                              <input type="text" class="form-control" name="from_email"
                                  value="{{ get_option('from_email') }}">
                          </div>
                      </div>

                      <div class="col-lg-6">
                          <div class="form-group">
                              <label class="control-label my-2">{{ _lang('From Name') }}</label>
                              <input type="text" class="form-control" name="from_name"
                                  value="{{ get_option('from_name') }}">
                          </div>
                      </div>

                      <div class="col-lg-6">
                          <div class="form-group">
                              <label class="control-label my-2">{{ _lang('SMTP Host') }}</label>
                              <input type="text" class="form-control smtp" name="smtp_host"
                                  value="{{ get_option('smtp_host') }}">
                          </div>
                      </div>

                      <div class="col-lg-6">
                          <div class="form-group">
                              <label class="control-label my-2">{{ _lang('SMTP Port') }}</label>
                              <input type="text" class="form-control smtp" name="smtp_port"
                                  value="{{ get_option('smtp_port') }}">
                          </div>
                      </div>

                      <div class="col-lg-6">
                          <div class="form-group">
                              <label class="control-label my-2">{{ _lang('SMTP Username') }}</label>
                              <input type="text" class="form-control smtp" name="smtp_username"
                                  value="{{ get_option('smtp_username') }}">
                          </div>
                      </div>

                      <div class="col-lg-6">
                          <div class="form-group">
                              <label class="control-label my-2">{{ _lang('SMTP Password') }}</label>
                              <input type="password" class="form-control smtp" name="smtp_password"
                                  value="{{ get_option('smtp_password') }}">
                          </div>
                      </div>

                      <div class="col-lg-6">
                          <div class="form-group">
                              <label class="control-label my-2">{{ _lang('SMTP Encryption') }}</label>
                              <select class="form-select smtp" name="smtp_encryption">
                                  <option value="ssl"
                                      {{ get_option('smtp_encryption') == 'ssl' ? 'selected' : '' }}>
                                      {{ _lang('SSL') }}</option>
                                  <option value="tls"
                                      {{ get_option('smtp_encryption') == 'tls' ? 'selected' : '' }}>
                                      {{ _lang('TLS') }}</option>
                              </select>
                          </div>
                      </div>

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
