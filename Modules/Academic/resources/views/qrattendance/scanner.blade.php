@extends('layouts.backend')

@section('content')
    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-header">
                <h4 class="mb-0">
                    QR Code Attendance Scanner
                </h4>
            </div>

            <div class="card-body">
                <div class="row g-4">

                    {{-- CAMERA --}}
                    <div class="col-md-6">

                        <div class="card border">

                            <div class="card-header text-center">
                                Scan Student Smart Card
                            </div>

                            <div class="card-body">
                                <div id="reader"></div>

                                <div id="cameraError" class="alert alert-danger mt-3 d-none">
                                    Camera not found or permission denied.
                                </div>

                                <button id="switchCamera" class="btn btn-dark w-100 mt-3">
                                    Switch Camera
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- RESULT --}}
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body text-center">
                                <img src="{{ asset('images/attendance_card.png') }}" width="250" class="mb-3">

                                <h4 id="studentName">
                                    Waiting for scan...
                                </h4>

                                <div id="resultMessage" class="mt-3">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-4">
                {{-- MANUAL --}}

                <div class="row">
                    <div class="col-md-9">
                        <input id="manualQR" class="form-control form-control-lg" placeholder="Enter QR Code manually">
                    </div>

                    <div class="col-md-3">
                        <button onclick="manualSubmit()" class="btn btn-primary btn-lg w-100">
                            Submit
                        </button>
                    </div>

                </div>

            </div>

        </div>

    </div>

    {{-- STYLE --}}
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

    {{-- QR LIBRARY --}}
    <script src="{{ asset('assets/vendor/html5-qrcode/html5-qrcode.min.js') }}"></script>

    <script>
        let scanner = null;
        let cameras = [];
        let currentCameraIndex = 0;
        let isScanning = false;
        let processing = false;

        const scannerConfig = {
            fps: 12,
            qrbox: {
                width: 280,
                height: 280
            },
            aspectRatio: 1.0
        };

        // START SYSTEM
        async function initScanner() {
            try {
                cameras = await Html5Qrcode.getCameras();
                if (!cameras.length) {
                    showCameraError();
                    return;
                }
                // select back camera first
                let backCamera = cameras.find(camera =>
                    camera.label
                    .toLowerCase()
                    .includes('back')
                );

                let selectedCamera = backCamera ?
                    backCamera.id :
                    cameras[0].id;
                currentCameraIndex =
                    cameras.findIndex(
                        c => c.id == selectedCamera
                    );

                startScanner(selectedCamera);
            } catch (error) {
                showCameraError();
            }
        }

        // START CAMERA
        function startScanner(cameraId) {
            if (scanner) {
                try {
                    scanner.clear();
                } catch (e) {}
            }

            scanner = new Html5Qrcode("reader");

            scanner.start(
                    cameraId,
                    scannerConfig,
                    function(decodedText) {
                        if (processing)
                            return;
                        processing = true;
                        submitAttendance(decodedText);
                    },

                    function(error) {}
                )

                .then(() => {
                    isScanning = true;
                })

                .catch(() => {
                    showCameraError();
                });
        }

        // STOP CAMERA
        async function stopScanner() {
            if (scanner && isScanning) {
                try {
                    await scanner.stop();
                } catch (e) {}
                isScanning = false;
            }
        }

        // SWITCH CAMERA
        document
            .getElementById('switchCamera')
            .onclick = async function() {
                await stopScanner();
                currentCameraIndex++;
                if (currentCameraIndex >= cameras.length) {
                    currentCameraIndex = 0;
                }
                startScanner(
                    cameras[currentCameraIndex].id
                );
            };

        // SEND ATTENDANCE
        async function submitAttendance(qr) {
            await stopScanner();
            fetch("{{ route('qrattendance.scan') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },

                    body: JSON.stringify({
                        qr_code: qr
                    })
                })
                .then(async response => {
                    let data = await response.json();
                    if (response.status === 422) {
                        let errors =
                            Object.values(data.errors)
                            .flat()
                            .join("<br>");

                        showMessage(
                            errors,
                            "danger"
                        );


                    } else if (data.success) {
                        document
                            .getElementById('studentName')
                            .innerHTML = `
            <span class="text-success">
            ✓ Attendance Done
            </span>
            `;
                        showMessage(
                            data.message,
                            "success"
                        );

                        playSuccess();
                    } else {
                        showMessage(
                            data.message ??
                            "Something went wrong",
                            "danger"
                        );
                    }
                })

                .catch(() => {
                    showMessage(
                        "Server error",
                        "danger"
                    );
                })

                .finally(() => {
                    processing = false;

                    setTimeout(() => {


                        startScanner(

                            cameras[currentCameraIndex].id


                        );


                    }, 1500);

                });

        }


        // MESSAGE
        function showMessage(message, type) {
            document
                .getElementById('resultMessage')
                .innerHTML = `



<div class="alert alert-${type}">

${message}

</div>


`;

        }


        // CAMERA ERROR

        function showCameraError() {
            document
                .getElementById('cameraError')
                .classList
                .remove('d-none');
        }

        // MANUAL



        function manualSubmit() {



            let qr = document
                .getElementById('manualQR')
                .value.trim();

            if (qr) {
                submitAttendance(qr);
                document
                    .getElementById('manualQR')
                    .value = '';

            }

        }

        // SOUND
        function playSuccess() {



            let audio = new Audio(

                "https://actions.google.com/sounds/v1/alarms/beep_short.ogg"

            );


            audio.play();

        }

        // INIT
        initScanner();
    </script>
@endsection
