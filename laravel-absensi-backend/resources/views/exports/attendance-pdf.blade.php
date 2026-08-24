<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; }
        h2 { margin-bottom: 2px; }
        p.subtitle { margin-top: 0; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        th { background-color: #f3f4f6; font-weight: bold; }
        .status-hadir { color: #15803d; font-weight: bold; }
        .status-telat { color: #c2410c; font-weight: bold; }
        .status-izin, .status-sakit { color: #a16207; font-weight: bold; }
        .status-alpha { color: #b91c1c; font-weight: bold; }
    </style>
</head>
<body>
    <h2>Rekap Absensi Siswa</h2>
    <p class="subtitle">Periode: {{ \Carbon\Carbon::parse($dari)->format('d M Y') }} — {{ \Carbon\Carbon::parse($sampai)->format('d M Y') }}</p>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>NISN</th>
                <th>Nama Siswa</th>
                <th>Kelas</th>
                <th>Masuk</th>
                <th>Pulang</th>
                <th>Status</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($attendances as $item)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</td>
                    <td>{{ $item->student->nisn ?? '-' }}</td>
                    <td>{{ $item->student->name ?? '-' }}</td>
                    <td>{{ $item->student->schoolClass->name ?? '-' }}</td>
                    <td>{{ $item->scanned_at ? \Carbon\Carbon::parse($item->scanned_at)->format('H:i') : '-' }}</td>
                    <td>{{ $item->waktu_pulang ? \Carbon\Carbon::parse($item->waktu_pulang)->format('H:i') : '-' }}</td>
                    <td class="status-{{ $item->status }}">{{ ucfirst($item->status) }}</td>
                    <td>{{ $item->note ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align:center;">Tidak ada data pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>