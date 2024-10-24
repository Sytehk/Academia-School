<div id="zoom" class="tab-pane fade">

    <div class="card border p-4">

        <div class="card-header">
            <h5>{{ _lang('Zoom Settings') }}</h5>
        </div>

        <div class="card-body">

            <form method="POST" action="{{ url('administration/general_settings/update') }}">

                @csrf

                <div class="row">

                    <div class="col-lg-4">
                        <label>{{ _lang('Zoom Account ID') }}</label>

                        <input type="text" name="zoom_account_id" class="form-control"
                            value="{{ get_option('zoom_account_id') }}">
                    </div>

                    <div class="col-lg-4">
                        <label>{{ _lang('Zoom Client Key') }}</label>

                        <input type="text" name="zoom_client_key" class="form-control"
                            value="{{ get_option('zoom_client_key') }}">
                    </div>

                    <div class="col-lg-4">
                        <label>{{ _lang('Zoom Client Secret') }}</label>

                        <input type="text" name="zoom_client_secret" class="form-control"
                            value="{{ get_option('zoom_client_secret') }}">
                    </div>

                    <div class="col-lg-12 mt-4">
                        <button class="btn btn-primary">
                            {{ _lang('Save Settings') }}
                        </button>
                    </div>

                </div>

            </form>

        </div>

    </div>

</div>
