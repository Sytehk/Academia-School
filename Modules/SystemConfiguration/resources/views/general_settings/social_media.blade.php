 <div id="socialMedia" class="tab-pane fade show">
     <div class="card card-default border p-5 my-2">
         <div class="card-header">
             <span class="card-title">{{ _lang('Social Media Settings') }}</span>
         </div>

         <div class="">
             <form method="post" action="{{ url('administration/general_settings/update') }}"
                 enctype="multipart/form-data">
                 @csrf

                 <div class="row">
                     <div class="col-lg-6">
                         <div class="form-group">
                             <label class="control-label my-2">{{ _lang('Google Map') }}</label>
                             <textarea class="form-control" name="google_map">{{ get_option('google_map') }}</textarea>
                         </div>
                     </div>
                     <div class="col-lg-6">
                         <div class="form-group">
                             <label class="control-label my-2">{{ _lang('On Google Map') }}</label>
                             <textarea class="form-control" name="on_google_map">{{ get_option('on_google_map') }}</textarea>
                         </div>
                     </div>
                     <div class="col-lg-6">
                         <div class="form-group">
                             <label class="control-label my-2">{{ _lang('Facebook Link') }}</label>
                             <textarea class="form-control" name="facebook_link">{{ get_option('facebook_link') }}</textarea>
                         </div>
                     </div>

                     <div class="col-lg-6">
                         <div class="form-group">
                             <label class="control-label my-2">{{ _lang('Google Plus Link') }}</label>
                             <textarea class="form-control" name="google_plus_link">{{ get_option('google_plus_link') }}</textarea>
                         </div>
                     </div>

                     <div class="col-lg-6">
                         <div class="form-group">
                             <label class="control-label my-2">{{ _lang('Youtube Link') }}</label>
                             <textarea class="form-control" name="youtube_link">{{ get_option('youtube_link') }}</textarea>
                         </div>
                     </div>

                     <div class="col-lg-6">
                         <div class="form-group">
                             <label class="control-label my-2">{{ _lang('What`s App Link') }}</label>
                             <textarea class="form-control" name="whats_app_link">{{ get_option('whats_app_link') }}</textarea>
                         </div>
                     </div>

                     <div class="col-lg-6">
                         <div class="form-group">
                             <label class="control-label my-2">{{ _lang('Twitter Link') }}</label>
                             <textarea class="form-control" name="twitter_link">{{ get_option('twitter_link') }}</textarea>
                         </div>
                     </div>

                     <div class="col-lg-6">
                         <div class="form-group">
                             <label class="control-label my-2">{{ _lang('LinkedIn Link') }}</label>
                             <textarea class="form-control" name="linkedin_link">{{ get_option('linkedin_link') }}</textarea>
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
