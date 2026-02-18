<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Barangay Document')</title>
    <style>
        /* Essential for PDF rendering */
        @page {
            size: A4;
            margin: 0;
        }

        body {
            font-family: 'Times New Roman', serif;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact;
        }

        .document-wrapper {
            width: 210mm;
            /* A4 Width */
            min-height: 297mm;
            /* A4 Height */
            padding: 20mm;
            box-sizing: border-box;
            position: relative;
            background: #fff;
        }

        /* The Header Section */
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 30px;
        }

        .seal-img {
            width: 80px;
            height: 80px;
            object-fit: contain;
        }

        .header-text h1 {
            font-size: 14pt;
            margin: 0;
            text-transform: uppercase;
        }

        .header-text h2 {
            font-size: 16pt;
            margin: 5px 0;
            font-weight: bold;
            text-transform: uppercase;
        }

        .header-text p {
            font-size: 11pt;
            margin: 0;
        }

        /* Content Area */
        .content {
            font-size: 12pt;
            line-height: 1.6;
            text-align: justify;
        }

        /* Watermark */
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80pt;
            color: rgba(0, 0, 0, 0.05);
            font-weight: bold;
            z-index: 0;
            pointer-events: none;
            white-space: nowrap;
        }

        /* Footer */
        .footer {
            position: absolute;
            bottom: 20mm;
            left: 20mm;
            right: 20mm;
            text-align: center;
            font-size: 10pt;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }
    </style>
</head>

<body>
    <div class="document-wrapper">
        <div class="watermark">OFFICIAL COPY</div>

        <header class="header">
            {{-- City/Municipality Seal (Left) --}}
            <x-documents.components.seal :image="$seal" />
            <div class="header-text">
                <p>Republic of the Philippines</p>
                <p>Province of {{ $barangay->province }}</p>
                <p>City of {{ $barangay->city }}</p>
                <h1>Office of the Sangguniang Barangay</h1>
                <h2>Barangay {{ $barangay->name }}</h2>
            </div>

            {{-- Barangay Seal (Right - The private one we fetched) --}}
            <x-documents.components.seal :image="$seal" />
        </header>

        <main class="content">
            @yield('content')
        </main>

        <footer class="footer">
            <p>{{ $barangay->address }} | Contact: {{ $barangay->contact_number }}</p>
            <p>Generated on {{ now()->format('F d, Y h:i A') }}</p>
        </footer>
    </div>
</body>

</html>