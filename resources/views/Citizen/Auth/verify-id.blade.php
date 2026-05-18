<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify ID | Khadamati</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
</head>

<body style="background: #f3f4f6; min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: sans-serif;">

    <div style="width: 650px; background: white; border-radius: 16px; border: 0.5px solid #e5e7eb; box-shadow: 0 4px 24px rgba(0,0,0,0.06); overflow: hidden;">

        <div style="background: #1e3a5f; padding: 24px 32px; text-align: center;">
            <i class="ti ti-id" style="color: white; font-size: 40px; margin-bottom: 8px; display: block;"></i>
            <p style="color: white; font-size: 20px; font-weight: 500; margin: 0;">Identity Verification</p>
            <p style="color: rgba(255,255,255,0.7); font-size: 13px; margin: 4px 0 0;">Please upload your National ID card</p>
        </div>

        <div style="padding: 32px;">

            {{-- Display all errors --}}
            @if(session('error'))
                <div style="background: #fef2f2; border: 0.5px solid #fecaca; color: #991b1b; padding: 12px; border-radius: 8px; font-size: 13px; margin-bottom: 24px;">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div style="background: #fef2f2; border: 0.5px solid #fecaca; color: #991b1b; padding: 12px; border-radius: 8px; font-size: 13px; margin-bottom: 24px;">
                    @foreach($errors->all() as $error)
                        <p style="margin: 4px 0;">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            {{-- Success messages --}}
            @if(session('success'))
                <div style="background: #f0fdf4; border: 0.5px solid #bbf7d0; color: #166534; padding: 12px; border-radius: 8px; font-size: 13px; margin-bottom: 24px;">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('citizen.auth.verify-id.process') }}" enctype="multipart/form-data">
                @csrf

                <div style="margin-bottom: 24px;">
                    <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 8px;">
                        Front Side of ID <span style="color: #ef4444;">*</span>
                    </label>
                    <div id="frontPreview" style="border: 2px dashed #e5e7eb; border-radius: 12px; padding: 32px; text-align: center; cursor: pointer; background: #f9fafb; transition: all 0.2s;">
                        <i class="ti ti-cloud-upload" style="font-size: 32px; color: #9ca3af; margin-bottom: 8px; display: block;"></i>
                        <p style="color: #6b7280; font-size: 13px; margin: 0;">Click to upload front side</p>
                        <p style="color: #9ca3af; font-size: 11px; margin: 4px 0 0;">JPG, PNG or PDF (Max 5MB)</p>
                    </div>
                    <input type="file" name="id_front" id="id_front" accept="image/*" required style="display: none;">
                </div>

                <div style="margin-bottom: 28px;">
                    <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 8px;">
                        Back Side of ID <span style="color: #ef4444;">*</span>
                    </label>
                    <div id="backPreview" style="border: 2px dashed #e5e7eb; border-radius: 12px; padding: 32px; text-align: center; cursor: pointer; background: #f9fafb; transition: all 0.2s;">
                        <i class="ti ti-cloud-upload" style="font-size: 32px; color: #9ca3af; margin-bottom: 8px; display: block;"></i>
                        <p style="color: #6b7280; font-size: 13px; margin: 0;">Click to upload back side</p>
                        <p style="color: #9ca3af; font-size: 11px; margin: 4px 0 0;">JPG, PNG or PDF (Max 5MB)</p>
                    </div>
                    <input type="file" name="id_back" id="id_back" accept="image/*" required style="display: none;">
                </div>

                <div style="background: #f0fdf4; border: 0.5px solid #bbf7d0; padding: 12px; border-radius: 8px; margin-bottom: 24px;">
                    <p style="font-size: 12px; color: #166534; margin: 0; display: flex; align-items: center; gap: 6px;">
                        <i class="ti ti-shield-lock" style="font-size: 14px;"></i>
                        Your documents are encrypted and used only for identity verification.
                    </p>
                </div>

                <button type="submit"
                        style="width: 100%; padding: 12px; background: #1e3a5f; color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 500; cursor: pointer;"
                        onmouseover="this.style.background='#162d4a'"
                        onmouseout="this.style.background='#1e3a5f'">
                    Verify Identity →
                </button>
            </form>

            <div style="text-align: center; margin-top: 24px;">
                <a href="{{ route('citizen.auth.register') }}" style="font-size: 13px; color: #6b7280; text-decoration: none;">
                    ← Back to registration
                </a>
            </div>

        </div>

    </div>

    <script>
        const frontInput = document.getElementById('id_front');
        const frontPreview = document.getElementById('frontPreview');
        const backInput = document.getElementById('id_back');
        const backPreview = document.getElementById('backPreview');

        frontPreview.addEventListener('click', () => frontInput.click());
        backPreview.addEventListener('click', () => backInput.click());

        frontInput.addEventListener('change', function(e) {
            if (e.target.files[0]) {
                frontPreview.innerHTML = `
                    <i class="ti ti-file-check" style="font-size: 32px; color: #1e3a5f; margin-bottom: 8px; display: block;"></i>
                    <p style="color: #1e3a5f; font-size: 13px; margin: 0;">${e.target.files[0].name}</p>
                    <p style="color: #6b7280; font-size: 11px; margin: 4px 0 0;">Click to change</p>
                `;
                frontPreview.style.borderColor = '#1e3a5f';
                frontPreview.style.background = '#eff6ff';
            }
        });

        backInput.addEventListener('change', function(e) {
            if (e.target.files[0]) {
                backPreview.innerHTML = `
                    <i class="ti ti-file-check" style="font-size: 32px; color: #1e3a5f; margin-bottom: 8px; display: block;"></i>
                    <p style="color: #1e3a5f; font-size: 13px; margin: 0;">${e.target.files[0].name}</p>
                    <p style="color: #6b7280; font-size: 11px; margin: 4px 0 0;">Click to change</p>
                `;
                backPreview.style.borderColor = '#1e3a5f';
                backPreview.style.background = '#eff6ff';
            }
        });
    </script>

</body>
</html>