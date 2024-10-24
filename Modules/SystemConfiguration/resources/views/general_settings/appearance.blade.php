<div id="appearance" class="tab-pane fade">

    <div class="card border p-4">

        <div class="card-header">
            <h5>{{ _lang('Appearance Settings') }}</h5>
        </div>

        <div class="card-body">

            <form method="POST" action="{{ url('administration/general_settings/update') }}">

                @csrf

                <div class="row">

                    <div class="col-lg-3">
                        <label>Primary Color</label>
                        <input type="color" class="form-control form-control-color" name="primary_color"
                            value="{{ get_option('primary_color', '#0d6efd') }}">
                    </div>

                    <div class="col-lg-3">
                        <label>Secondary Color</label>
                        <input type="color" class="form-control form-control-color" name="secondary_color"
                            value="{{ get_option('secondary_color', '#6610f2') }}">
                    </div>

                    <div class="col-lg-3">
                        <label>Text Color</label>
                        <input type="color" class="form-control form-control-color" name="text_color"
                            value="{{ get_option('text_color', '#000000') }}">
                    </div>

                    <div class="col-lg-3">
                        <label>Sidebar Color</label>
                        <input type="color" class="form-control form-control-color" name="sidebar_color"
                            value="{{ get_option('sidebar_color', '#212529') }}">
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
