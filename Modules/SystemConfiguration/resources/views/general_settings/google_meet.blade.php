<div id="googleMeet" class="tab-pane fade">

    <div class="card border p-4">

        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>{{ _lang('Google Meet Settings') }}</h5>

            <div>
                @if (get_option('google_meet_access_token'))
                    <span class="badge bg-success">{{ _lang('Connected') }}</span>
                    @if (get_option('google_meet_token_expiry'))
                        <small class="text-muted">
                            {{ _lang('Token expires') }}: {{ get_option('google_meet_token_expiry') }}
                        </small>
                    @endif
                @else
                    <span class="badge bg-secondary">{{ _lang('Not Connected') }}</span>
                @endif
            </div>
        </div>

        <div class="card-body">

            <form method="POST" action="{{ url('administration/general_settings/update') }}">

                @csrf

                <div class="row">

                    <div class="col-lg-3">
                        <label>{{ _lang('Enable Google Meet') }}</label>
                        <select name="google_meet_status" class="form-control">
                            <option value="1" @selected(get_option('google_meet_status') == '1')>{{ _lang('Enabled') }}</option>
                            <option value="0" @selected(get_option('google_meet_status') != '1')>{{ _lang('Disabled') }}</option>
                        </select>
                    </div>

                    <div class="col-lg-4">
                        <label>{{ _lang('Client ID') }}</label>
                        <input type="text" name="google_meet_client_id" class="form-control"
                            value="{{ get_option('google_meet_client_id') }}">
                    </div>

                    <div class="col-lg-4">
                        <label>{{ _lang('Client Secret') }}</label>
                        <input type="text" name="google_meet_client_secret" class="form-control"
                            value="{{ get_option('google_meet_client_secret') }}">
                    </div>

                    <div class="col-lg-4 mt-3">
                        <label>{{ _lang('Project ID') }}</label>
                        <input type="text" name="google_meet_project_id" class="form-control"
                            value="{{ get_option('google_meet_project_id') }}">
                    </div>

                    <div class="col-lg-4 mt-3">
                        <label>{{ _lang('Redirect URI') }}</label>
                        <input type="text" name="google_meet_redirect_uri" class="form-control"
                            value="{{ get_option('google_meet_redirect_uri') ?: url('google-meet/oauth/callback') }}"
                            placeholder="{{ url('google-meet/oauth/callback') }}">
                        <small class="text-muted">{{ _lang('Register this exact URL as an authorized redirect URI in your Google Cloud OAuth client.') }}</small>
                    </div>

                    <div class="col-lg-4 mt-3">
                        <label>{{ _lang('API Key') }}</label>
                        <input type="text" name="google_meet_api_key" class="form-control"
                            value="{{ get_option('google_meet_api_key') }}">
                    </div>

                    <div class="col-lg-12 mt-3">
                        <label>{{ _lang('Service Account JSON') }}</label>
                        <textarea name="google_meet_service_account_json" rows="4" class="form-control"
                            placeholder="{{ _lang('Optional - only needed for domain-wide delegation server-to-server setups') }}">{{ get_option('google_meet_service_account_json') }}</textarea>
                    </div>

                    <div class="col-lg-4 mt-3">
                        <label>{{ _lang('Calendar ID') }}</label>
                        <input type="text" name="google_meet_calendar_id" class="form-control"
                            value="{{ get_option('google_meet_calendar_id') }}" placeholder="primary">
                    </div>

                    <div class="col-lg-3 mt-3">
                        <label>{{ _lang('Default Duration (minutes)') }}</label>
                        <input type="number" name="google_meet_default_duration" class="form-control"
                            value="{{ get_option('google_meet_default_duration') ?: 40 }}">
                    </div>

                    <div class="col-lg-3 mt-3">
                        <label>{{ _lang('Default Timezone') }}</label>
                        <input type="text" name="google_meet_default_timezone" class="form-control"
                            value="{{ get_option('google_meet_default_timezone') ?: 'UTC' }}">
                    </div>

                    <div class="col-lg-2 mt-3">
                        <label>{{ _lang('Default Visibility') }}</label>
                        <input type="text" name="google_meet_default_permission" class="form-control"
                            value="{{ get_option('google_meet_default_permission') ?: 'teacher,student' }}">
                    </div>

                    <div class="col-lg-12 mt-4 d-flex gap-2">
                        <button class="btn btn-primary">
                            {{ _lang('Save Settings') }}
                        </button>

                        <a href="{{ route('google-meet.oauth.connect') }}" class="btn btn-outline-success">
                            {{ _lang('Connect Google Account') }}
                        </a>

                        <form action="{{ route('google-meet.test-connection') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary">
                                {{ _lang('Test Connection') }}
                            </button>
                        </form>
                    </div>

                </div>

            </form>

        </div>

    </div>

</div>
