@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('QR Attendance') }}
@endsection

@section('content')
    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-header">
                <h4 class="mb-0">{{ _lang('QR Code Attendance Scanner') }}</h4>
            </div>

            <div class="card-body">
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">{{ _lang('Class / Section') }}</label>
                        <select id="sectionSelect" class="form-select form-select-lg">
                            <option value="">{{ _lang('--Select Class / Section--') }}</option>
                            @foreach ($sections as $section)
                                <option value="{{ $section['section_id'] }}" data-class-id="{{ $section['class_id'] }}">
                                    {{ $section['class_name'] }} - {{ $section['section_name'] }}
                                </option>
                            @endforeach
                        </select>
                        @if (empty($sections))
                            <small class="text-danger">{{ _lang('You are not assigned as a subject or class teacher for any section yet.') }}</small>
                        @endif
                    </div>
                </div>

                <div id="scannerArea" class="row g-4 d-none">
                    {{-- CAMERA --}}
                    <div class="col-md-6">
                        <div class="card border">
                            <div class="card-header text-center">{{ _lang('Scan Student Smart Card') }}</div>
                            <div class="card-body">
                                <div id="reader"></div>
                                <div id="cameraError" class="alert alert-danger mt-3 d-none">
                                    {{ _lang('Camera not found or permission denied.') }}
                                </div>
                                <button id="switchCamera" class="btn btn-dark w-100 mt-3">{{ _lang('Switch Camera') }}</button>
                            </div>
                        </div>
                    </div>

                    {{-- RESULT --}}
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body text-center">
                                <h4 id="studentName">{{ _lang('Waiting for scan...') }}</h4>
                                <div id="resultMessage" class="mt-3"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-4">
                <div id="manualArea" class="row d-none">
                    <div class="col-md-9">
                        <input id="manualQR" class="form-control form-control-lg" placeholder="{{ _lang('Enter QR Code manually') }}">
                    </div>
                    <div class="col-md-3">
                        <button onclick="manualSubmit()" class="btn btn-primary btn-lg w-100">{{ _lang('Submit') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        #reader {
            width: 100%;
        }

        #reader video {
            width: 100% !important;
            border-radius: 15px;
        }

        #reader__scan_region {
            min-height: 250px;
        }
    </style>

    <script src="{{ asset('assets/vendor/html5-qrcode/html5-qrcode.min.js') }}"></script>

    <script>
        let scanner = null;
        let cameras = [];
        let currentCameraIndex = 0;
        let isScanning = false;
        let processing = false;
        let selectedClassId = null;
        let selectedSectionId = null;

        const scannerConfig = {
            fps: 12,
            qrbox: { width: 280, height: 280 },
            aspectRatio: 1.0
        };

        document.getElementById('sectionSelect').addEventListener('change', function() {
            const option = this.options[this.selectedIndex];
            selectedSectionId = this.value || null;
            selectedClassId = option ? option.dataset.classId : null;

            const scannerArea = document.getElementById('scannerArea');
            const manualArea = document.getElementById('manualArea');

            if (selectedSectionId) {
                scannerArea.classList.remove('d-none');
                manualArea.classList.remove('d-none');
                initScanner();
            } else {
                scannerArea.classList.add('d-none');
                manualArea.classList.add('d-none');
                stopScanner();
            }
        });

        async function initScanner() {
            try {
                cameras = await Html5Qrcode.getCameras();
                if (!cameras.length) {
                    showCameraError();
                    return;
                }
                let backCamera = cameras.find(camera => camera.label.toLowerCase().includes('back'));
                let selectedCamera = backCamera ? backCamera.id : cameras[0].id;
                currentCameraIndex = cameras.findIndex(c => c.id == selectedCamera);
                startScanner(selectedCamera);
            } catch (error) {
                showCameraError();
            }
        }

        function startScanner(cameraId) {
            if (scanner) {
                try { scanner.clear(); } catch (e) {}
            }

            scanner = new Html5Qrcode("reader");
            scanner.start(
                cameraId,
                scannerConfig,
                function(decodedText) {
                    if (processing) return;
                    processing = true;
                    submitAttendance(decodedText);
                },
                function(error) {}
            ).then(() => {
                isScanning = true;
            }).catch(() => {
                showCameraError();
            });
        }

        async function stopScanner() {
            if (scanner && isScanning) {
                try { await scanner.stop(); } catch (e) {}
                isScanning = false;
            }
        }

        document.getElementById('switchCamera').onclick = async function() {
            await stopScanner();
            currentCameraIndex++;
            if (currentCameraIndex >= cameras.length) currentCameraIndex = 0;
            startScanner(cameras[currentCameraIndex].id);
        };

        async function submitAttendance(qr) {
            await stopScanner();
            fetch("{{ url('teacher/qr-attendance/scan') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    qr_code: qr,
                    class_id: selectedClassId,
                    section_id: selectedSectionId
                })
            })
                .then(async response => {
                    let data = await response.json();
                    if (response.status === 422 && data.errors) {
                        showMessage(Object.values(data.errors).flat().join("<br>"), "danger");
                    } else if (data.status) {
                        document.getElementById('studentName').innerHTML = `<span class="text-success">✓ ${data.message}</span>`;
                        showMessage(data.message, "success");
                        playSuccess();
                    } else {
                        showMessage(data.message ?? "Something went wrong", "danger");
                    }
                })
                .catch(() => {
                    showMessage("Server error", "danger");
                })
                .finally(() => {
                    processing = false;
                    setTimeout(() => {
                        if (selectedSectionId && cameras.length) {
                            startScanner(cameras[currentCameraIndex].id);
                        }
                    }, 1500);
                });
        }

        function showMessage(message, type) {
            document.getElementById('resultMessage').innerHTML = `<div class="alert alert-${type}">${message}</div>`;
        }

        function showCameraError() {
            document.getElementById('cameraError').classList.remove('d-none');
        }

        function manualSubmit() {
            let qr = document.getElementById('manualQR').value.trim();
            if (qr) {
                submitAttendance(qr);
                document.getElementById('manualQR').value = '';
            }
        }

        function playSuccess() {
            let audio = new Audio("https://actions.google.com/sounds/v1/alarms/beep_short.ogg");
            audio.play();
        }
    </script>
@endsection
