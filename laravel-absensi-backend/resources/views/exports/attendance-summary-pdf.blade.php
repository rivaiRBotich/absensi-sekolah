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
    </style>
</head>
<body>
    <h2>Rekap Per Siswa</h2>
    <p class="subtitle">Periode: {{ \Carbon\Carbon::parse($periode['dari'])->format('d M Y') }} — {{ \Carbon\Carbon::parse($periode['sampai'])->format('d M Y') }}</p>

    <table>
        <thead>
            <tr>
                <th>Nama Siswa</th>
                <th>Kelas</th>
                <th>Hadir</th>
                <th>Telat</th>
                <th>Izin</th>
                <th>Sakit</th>
                <th>Alpha</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $item)
                <tr>
                    <td>{{ $item['nama'] }}</td>
                    <td>{{ $item['kelas'] }}</td>
                    <td>{{ $item['hadir'] }}</td>
                    <td>{{ $item['telat'] }}</td>
                    <td>{{ $item['izin'] }}</td>
                    <td>{{ $item['sakit'] }}</td>
                    <td>{{ $item['alpha'] }}</td>
                </tr>
            @empty
                <tr><td colspan="7" style="text-align:center;">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>