 <div id="general" class="tab-pane fade show active">
     <div class="p-5 my-2 border card card-default">
         <div class="card-header">
             <span class="card-title">{{ _lang('General Settings') }}</span>
         </div>

         <div class="">
             <form method="post" action="{{ url('administration/general_settings/update') }}"
                 enctype="multipart/form-data">
                 @csrf

                 <div class="row">
                     <div class="col-lg-6">
                         <div class="form-group">
                             <label class="my-2 control-label">{{ _lang('Institute Name') }}</label>
                             <input type="text" class="form-control" name="school_name"
                                 value="{{ get_option('school_name') }}">
                         </div>
                     </div>

                     {{-- <div class="col-lg-6">
                         <div class="form-group">
                             <label for="theme_id" class="my-2 control-label">{{ _lang('Theme') }}</label>
                             <select id="theme_id" name="theme_id" class="form-control select2">
                                 <option value="yes" {{ get_option('theme_id') == 'yes' ? 'selected' : '' }}>Yes
                                 </option>
                                 <option value="no" {{ get_option('theme_id') == 'no' ? 'selected' : '' }}>No
                                 </option>
                             </select>
                         </div>
                     </div> --}}

                     <div class="col-lg-6">
                         <div class="form-group">
                             <label class="my-2 control-label">{{ _lang('Site Title') }}</label>
                             <input type="text" class="form-control" name="site_title"
                                 value="{{ get_option('site_title') }}">
                         </div>
                     </div>

                     <div class="col-lg-6">
                         <div class="form-group">
                             <label class="my-2 control-label">{{ _lang('Tagline / Footer Subtitle') }}</label>
                             <input type="text" class="form-control" name="tagline"
                                 value="{{ get_option('tagline') }}"
                                 placeholder="{{ _lang('e.g. Modern SaaS-based institute management system...') }}">
                             <small
                                 class="text-muted">{{ _lang('Shown under the school name in the website footer.') }}</small>
                         </div>
                     </div>

                     <div class="col-lg-6">
                         <div class="form-group">
                             <label class="my-2 control-label">{{ _lang('SEO Meta Title') }}</label>
                             <input type="text" class="form-control" name="meta_title"
                                 value="{{ get_option('meta_title') }}"
                                 placeholder="{{ _lang('Falls back to Site Title if left blank') }}">
                         </div>
                     </div>

                     <div class="col-lg-6">
                         <div class="form-group">
                             <label class="my-2 control-label">{{ _lang('SEO Meta Description') }}</label>
                             <textarea class="form-control" name="meta_description" rows="3">{{ get_option('meta_description') }}</textarea>
                         </div>
                     </div>

                     <div class="col-lg-6">
                         <div class="form-group">
                             <label class="my-2 control-label">{{ _lang('SEO Meta Keywords') }}</label>
                             <input type="text" class="form-control" name="meta_keywords"
                                 value="{{ get_option('meta_keywords') }}" placeholder="school, admission, result">
                         </div>
                     </div>

                     <div class="col-lg-6">
                         <div class="form-group">
                             <label
                                 class="my-2 control-label">{{ _lang('SEO Social Share Image (OG Image) URL') }}</label>
                             <input type="text" class="form-control" name="og_image"
                                 value="{{ get_option('og_image') }}" placeholder="https://.../share-image.jpg">
                             <small
                                 class="text-muted">{{ _lang('Shown as the preview image when the website is shared on social media.') }}</small>
                         </div>
                     </div>

                     <div class="col-lg-6">
                         <div class="form-group">
                             <label class="my-2 control-label">{{ _lang('Google Analytics 4 Measurement ID') }}</label>
                             <input type="text" class="form-control" name="ga4_id"
                                 value="{{ get_option('ga4_id') }}" placeholder="G-XXXXXXXXXX">
                         </div>
                     </div>

                     <div class="col-lg-6">
                         <div class="form-group">
                             <label class="my-2 control-label">{{ _lang('Google Tag Manager Container ID') }}</label>
                             <input type="text" class="form-control" name="gtm_id"
                                 value="{{ get_option('gtm_id') }}" placeholder="GTM-XXXXXXX">
                         </div>
                     </div>

                     <div class="col-lg-6">
                         <div class="form-group">
                             <label class="my-2 control-label">{{ _lang('Meta (Facebook) Pixel ID') }}</label>
                             <input type="text" class="form-control" name="meta_pixel_id"
                                 value="{{ get_option('meta_pixel_id') }}" placeholder="123456789012345">
                         </div>
                     </div>

                     <div class="col-lg-6">
                         <div class="form-group">
                             <label class="my-2 control-label">{{ _lang('Phone') }}</label>
                             <input type="text" class="form-control" name="phone"
                                 value="{{ get_option('phone') }}">
                         </div>
                     </div>
                     <div class="col-lg-6">
                         <div class="form-group">
                             <label class="my-2 control-label">{{ _lang('Tuition Fee') }}</label>
                             <input type="text" class="form-control" name="tuition_fee_phone"
                                 value="{{ get_option('tuition_fee_phone') }}">
                         </div>
                     </div>
                     <div class="col-lg-6">
                         <div class="form-group">
                             <label class="my-2 control-label">{{ _lang('Exam and result') }}</label>
                             <input type="text" class="form-control" name="exam_result_phone"
                                 value="{{ get_option('exam_result_phone') }}">
                         </div>
                     </div>

                     <div class="col-lg-6">
                         <div class="form-group">
                             <label class="my-2 control-label">{{ _lang('Currency') }}</label>
                             <select class="form-control select2" name="currency_id">
                                 <option value="">{{ _lang('--Select Currency--') }}</option>
                                 {!! create_option('currencies', 'id', 'name', get_option('currency_id'), ['status' => 1]) !!}
                             </select>
                             <small class="text-muted">{{ _lang('Symbol shown throughout fees, invoices, and reports.') }}</small>
                         </div>
                     </div>

                     <div class="col-lg-6">
                         <div class="form-group">
                             <label class="my-2 control-label">{{ _lang('Currency Symbol Position') }}</label>
                             <select class="form-control select2" name="currency_position">
                                 <option value="left" {{ get_option('currency_position') != 'right' ? 'selected' : '' }}>{{ _lang('Left (e.g. $100)') }}</option>
                                 <option value="right" {{ get_option('currency_position') == 'right' ? 'selected' : '' }}>{{ _lang('Right (e.g. 100$)') }}</option>
                             </select>
                         </div>
                     </div>

                     <div class="col-lg-6">
                         <div class="form-group">
                             <label class="my-2 control-label">{{ _lang('Attendance OUT After (Device)') }}</label>
                             <input type="time" class="form-control" name="attendance_out_after"
                                 value="{{ get_option('attendance_out_after') ?: '12:00' }}">
                             <small
                                 class="text-muted">{{ _lang('Biometric punches before this time are marked IN (arrived); punches at/after are marked OUT (left). Used for the parent WhatsApp notification.') }}</small>
                         </div>
                     </div>

                     <div class="col-lg-6">
                         <div class="form-group">
                             <label class="my-2 control-label">{{ _lang('Email') }}</label>
                             <input type="text" class="form-control" name="email"
                                 value="{{ get_option('email') }}">
                         </div>
                     </div>

                     <div class="col-lg-6">
                         <div class="form-group">
                             <label class="my-2 control-label">{{ _lang('Address') }}</label>
                             <textarea class="form-control" name="address">{{ get_option('address') }}</textarea>
                         </div>
                     </div>
                     <div class="col-lg-6">
                         <div class="form-group">
                             <label class="my-2 control-label">{{ _lang('EIIN') }}</label>
                             <textarea class="form-control" name="eiin_code">{{ get_option('eiin_code') }}</textarea>
                         </div>
                     </div>

                     <div class="col-lg-6">
                         <div class="form-group">
                             <label class="my-2 control-label">{{ _lang('Institute ID') }}</label>
                             <input type="text" class="form-control" name="institute_code"
                                 value="{{ get_option('institute_code') }}">
                         </div>
                     </div>

                     <div class="col-lg-6">
                         <div class="form-group">
                             <label class="my-2 control-label">{{ _lang('Transfer Certificate Fee') }}</label>
                             <input type="text" class="form-control" name="tc_amount"
                                 value="{{ get_option('tc_amount') }}">
                         </div>
                     </div>

                     <div class="col-lg-12">
                         <div class="form-group">
                             <label class="my-2 control-label">{{ _lang('Live Notice On Header') }}</label>
                             <textarea class="form-control" name="header_notice" cols="4" rows="4">{{ get_option('header_notice') }}</textarea>
                         </div>
                     </div>

                     <div class="col-lg-12">
                         <div class="form-group">
                             <label class="my-2 control-label">{{ _lang('CopyRight') }}</label>
                             <textarea class="form-control" name="copyright_text" cols="4" rows="4">{{ get_option('copyright_text') }}</textarea>
                         </div>
                     </div>

                     <div class="col-lg-6">
                         <div class="form-group">
                             <label for="admission_display_status"
                                 class="my-2 control-label">{{ _lang('Online Admission Display Status') }}</label>
                             <select id="admission_display_status" name="admission_display_status"
                                 class="form-control select2">
                                 <option value="yes"
                                     {{ get_option('admission_display_status') == 'yes' ? 'selected' : '' }}>Yes
                                 </option>
                                 <option value="no"
                                     {{ get_option('admission_display_status') == 'no' ? 'selected' : '' }}>No
                                 </option>
                             </select>
                         </div>
                     </div>

                     <div class="col-lg-6">
                         <div class="form-group">
                             <label for="exam_result_status"
                                 class="my-2 control-label">{{ _lang('Exam Results Display Status') }}</label>
                             <select id="exam_result_status" name="exam_result_status" class="form-control select2">
                                 <option value="yes"
                                     {{ get_option('exam_result_status') == 'yes' ? 'selected' : '' }}>Yes</option>
                                 <option value="no"
                                     {{ get_option('exam_result_status') == 'no' ? 'selected' : '' }}>No</option>
                             </select>
                         </div>
                     </div>

                     <div class="col-lg-6">
                         <div class="form-group">
                             <label class="my-2 control-label">{{ _lang('App Version') }}</label>
                             <input type="text" class="form-control" name="app_version"
                                 value="{{ get_option('app_version') }}">
                         </div>
                     </div>

                     <div class="col-lg-6">
                         <div class="form-group">
                             <label class="my-2 control-label">{{ _lang('App URL') }}</label>
                             <input type="text" class="form-control" name="app_url"
                                 value="{{ get_option('app_url') }}">
                         </div>
                     </div>

                     <div class="col-lg-6">
                         <div class="form-group">
                             <label class="my-2 control-label">{{ _lang('Play Store Link') }}</label>
                             <input type="text" class="form-control" name="play_store_link"
                                 value="{{ get_option('play_store_link') }}">
                         </div>
                     </div>

                     <div class="col-lg-6">
                         <div class="form-group">
                             <label class="my-2 control-label">{{ _lang('App Store Link') }}</label>
                             <input type="text" class="form-control" name="app_store_link"
                                 value="{{ get_option('app_store_link') }}">
                         </div>
                     </div>

                     <div class="form-group">
                         <div class="col-lg-12">
                             <button type="submit"
                                 class="mt-5 btn btn-primary">{{ _lang('Save Settings') }}</button>
                         </div>
                     </div>
                 </div>
             </form>
         </div>
     </div>
 </div>
