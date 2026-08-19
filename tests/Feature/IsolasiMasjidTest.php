<?php

namespace Tests\Feature;

use App\Models\Donasi;
use App\Models\GaleriAlbum;
use App\Models\Inventaris;
use App\Models\Jabatan;
use App\Models\JadwalPetugasSholat;
use App\Models\Jamaah;
use App\Models\KasTransaction;
use App\Models\Kegiatan;
use App\Models\Mosque;
use App\Models\Pengumuman;
use App\Models\QurbanPeserta;
use App\Models\QurbanSetoran;
use App\Models\Surat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class IsolasiMasjidTest extends TestCase
{
    use RefreshDatabase;

    private function masjid(string $nama): Mosque
    {
        return Mosque::create(['name' => $nama, 'status' => 'aktif']);
    }

    private function ketua(Mosque $masjid, string $email): User
    {
        return User::create([
            'name' => 'Ketua '.$masjid->name,
            'email' => $email,
            'password' => 'rahasia123',
            'role' => 'Ketua YMBPK',
            'mosque_id' => $masjid->id,
        ]);
    }

    /**
     * @return array{0: Mosque, 1: Mosque, 2: User}
     */
    private function duaMasjid(): array
    {
        $masjidA = $this->masjid('Masjid A');
        $masjidB = $this->masjid('Masjid B');

        return [$masjidA, $masjidB, $this->ketua($masjidA, 'ketua-a@example.com')];
    }

    /**
     * @return array{id: int, table: string, url: string}
     */
    private function buatMilikMasjid(string $modul, Mosque $masjid): array
    {
        $model = match ($modul) {
            'kas' => KasTransaction::create([
                'mosque_id' => $masjid->id,
                'tanggal' => now()->toDateString(),
                'jenis' => 'Infaq',
                'keterangan' => 'Kas masjid B',
                'pemasukan' => 100000,
                'pengeluaran' => 0,
            ]),
            'surat' => Surat::create([
                'mosque_id' => $masjid->id,
                'nomor' => 'B/001',
                'subjek' => 'Surat masjid B',
                'jenis' => 'Surat Undangan',
                'status' => 'Draft',
                'tanggal' => now()->toDateString(),
            ]),
            'inventaris' => Inventaris::create([
                'mosque_id' => $masjid->id,
                'nama' => 'Karpet masjid B',
                'kondisi' => 'Baik',
                'qty' => '1',
                'sumber' => 'Beli',
                'kode' => 'ISO-B-'.$masjid->id,
            ]),
            'jamaah' => Jamaah::create([
                'mosque_id' => $masjid->id,
                'nama' => 'Jamaah masjid B',
                'jenis_kelamin' => 'Laki-laki',
                'no_hp' => '0812',
                'status_jamaah' => 'Aktif',
            ]),
            'agenda' => Kegiatan::create([
                'mosque_id' => $masjid->id,
                'tanggal' => now()->toDateString(),
                'nama' => 'Kajian masjid B',
                'kategori' => 'Kajian',
                'status' => 'Akan Datang',
            ]),
            'jadwal_petugas' => JadwalPetugasSholat::create([
                'mosque_id' => $masjid->id,
                'tanggal' => now()->toDateString(),
                'sholat' => 'Subuh',
                'imam' => 'Imam B',
            ]),
            'donasi' => Donasi::create([
                'mosque_id' => $masjid->id,
                'tanggal' => now()->toDateString(),
                'donatur' => 'Donatur B',
                'kategori' => 'Infaq',
                'jenis' => 'Infaq Jumat',
                'tipe' => 'Uang',
                'nominal' => 50000,
                'status' => 'Berhasil',
            ]),
            'user' => User::create([
                'name' => 'Petugas B',
                'email' => 'petugas-b-'.$masjid->id.'@example.com',
                'password' => 'rahasia123',
                'role' => 'Petugas Zakat',
                'mosque_id' => $masjid->id,
            ]),
            default => throw new \InvalidArgumentException($modul),
        };

        $url = match ($modul) {
            'kas' => '/keuangan/kas-masjid/'.$model->id,
            'surat' => '/surat/'.$model->id,
            'inventaris' => '/inventaris/'.$model->id,
            'jamaah' => '/data-jamaah/'.$model->id,
            'agenda' => '/kegiatan/agenda/'.$model->id,
            'jadwal_petugas' => '/kegiatan/jadwal-petugas-sholat/'.$model->id,
            'donasi' => '/keuangan/infaq-sodaqoh/donasi/'.$model->id,
            'user' => '/pengaturan/user-management/'.$model->id,
        };

        $table = match ($modul) {
            'kas' => 'kas_transactions',
            'surat' => 'surats',
            'inventaris' => 'inventaris',
            'jamaah' => 'jamaah',
            'agenda' => 'kegiatans',
            'jadwal_petugas' => 'jadwal_petugas_sholats',
            'donasi' => 'donasis',
            'user' => 'users',
        };

        return ['id' => $model->id, 'table' => $table, 'url' => $url];
    }

    public static function modulHapusProvider(): array
    {
        return [
            'kas' => ['kas'],
            'surat' => ['surat'],
            'inventaris' => ['inventaris'],
            'jamaah' => ['jamaah'],
            'agenda' => ['agenda'],
            'jadwal_petugas' => ['jadwal_petugas'],
            'donasi' => ['donasi'],
            'user' => ['user'],
        ];
    }

    public function test_pengurus_tidak_bisa_mengubah_pengumuman_masjid_lain(): void
    {
        $masjidA = $this->masjid('Masjid A');
        $masjidB = $this->masjid('Masjid B');
        $ketuaA = $this->ketua($masjidA, 'ketua-a@example.com');

        $pengumumanB = Pengumuman::create([
            'mosque_id' => $masjidB->id,
            'judul' => 'Milik masjid B',
            'isi' => 'Rahasia internal B.',
            'status' => 'Aktif',
            'tanggal' => now()->toDateString(),
        ]);

        $this->actingAs($ketuaA)
            ->put('/pengumuman/'.$pengumumanB->id, [
                'judul' => 'Diserobot A',
                'isi' => 'Isi diubah dari masjid A.',
                'status' => 'Aktif',
                'tanggal' => now()->toDateString(),
            ])
            ->assertNotFound();

        $this->assertDatabaseHas('pengumumans', [
            'id' => $pengumumanB->id,
            'judul' => 'Milik masjid B',
        ]);
    }

    public function test_pengurus_tidak_bisa_menghapus_pengumuman_masjid_lain(): void
    {
        $masjidA = $this->masjid('Masjid A');
        $masjidB = $this->masjid('Masjid B');
        $ketuaA = $this->ketua($masjidA, 'ketua-a@example.com');

        $pengumumanB = Pengumuman::create([
            'mosque_id' => $masjidB->id,
            'judul' => 'Milik masjid B',
            'isi' => 'Jangan dihapus A.',
            'status' => 'Aktif',
            'tanggal' => now()->toDateString(),
        ]);

        $this->actingAs($ketuaA)
            ->deleteJson('/pengumuman/'.$pengumumanB->id)
            ->assertNotFound();

        $this->assertDatabaseHas('pengumumans', ['id' => $pengumumanB->id]);
    }

    public function test_pengurus_tidak_bisa_mencatat_setoran_qurban_masjid_lain(): void
    {
        $masjidA = $this->masjid('Masjid A');
        $masjidB = $this->masjid('Masjid B');
        $ketuaA = $this->ketua($masjidA, 'ketua-a@example.com');

        $pesertaB = QurbanPeserta::create([
            'mosque_id' => $masjidB->id,
            'nama' => 'Peserta B',
            'paket' => 'Kambing',
            'target' => 2500000,
            'mulai' => now()->toDateString(),
        ]);

        $this->actingAs($ketuaA)
            ->postJson('/keuangan/infaq-sodaqoh/setoran', [
                'qurban_peserta_id' => $pesertaB->id,
                'tanggal' => now()->toDateString(),
                'jumlah' => 100000,
                'petugas' => 'Petugas A',
            ])
            ->assertNotFound();

        $this->assertDatabaseMissing('qurban_setorans', [
            'qurban_peserta_id' => $pesertaB->id,
        ]);
    }

    public function test_setoran_qurban_tidak_bisa_dihubungkan_ke_anggota_peserta_lain(): void
    {
        $masjid = $this->masjid('Masjid A');
        $ketua = $this->ketua($masjid, 'ketua-a@example.com');

        $pesertaSetoran = QurbanPeserta::create([
            'mosque_id' => $masjid->id,
            'nama' => 'Peserta setoran',
            'paket' => 'Patungan Sapi',
            'target' => 3000000,
            'mulai' => now()->toDateString(),
        ]);
        $pesertaLain = QurbanPeserta::create([
            'mosque_id' => $masjid->id,
            'nama' => 'Peserta lain',
            'paket' => 'Patungan Sapi',
            'target' => 3000000,
            'mulai' => now()->toDateString(),
        ]);
        $anggotaPesertaLain = $pesertaLain->members()->create(['nama' => 'Anggota peserta lain']);
        $setoran = QurbanSetoran::create([
            'qurban_peserta_id' => $pesertaSetoran->id,
            'tanggal' => now()->toDateString(),
            'jumlah' => 100000,
            'petugas' => 'Petugas A',
        ]);

        $this->actingAs($ketua)
            ->putJson('/keuangan/infaq-sodaqoh/setoran/'.$setoran->id, [
                'qurban_peserta_member_id' => $anggotaPesertaLain->id,
                'tanggal' => now()->toDateString(),
                'jumlah' => 150000,
                'petugas' => 'Petugas A',
            ])
            ->assertNotFound();

        $this->assertDatabaseHas('qurban_setorans', [
            'id' => $setoran->id,
            'qurban_peserta_member_id' => null,
            'jumlah' => 100000,
        ]);
    }

    public function test_pengurus_bisa_menghapus_album_galeri_masjid_sendiri(): void
    {
        $masjid = $this->masjid('Masjid A');
        $ketua = $this->ketua($masjid, 'ketua-a@example.com');

        $album = GaleriAlbum::create([
            'mosque_id' => $masjid->id,
            'nama' => 'Album sendiri',
            'tanggal' => now()->toDateString(),
            'status' => 'draft',
        ]);

        $this->actingAs($ketua)
            ->deleteJson('/kegiatan/galeri/'.$album->id)
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('galeri_albums', ['id' => $album->id]);
    }

    public function test_pengurus_tidak_bisa_menghapus_album_galeri_masjid_lain(): void
    {
        $masjidA = $this->masjid('Masjid A');
        $masjidB = $this->masjid('Masjid B');
        $ketuaA = $this->ketua($masjidA, 'ketua-a@example.com');

        $albumB = GaleriAlbum::create([
            'mosque_id' => $masjidB->id,
            'nama' => 'Album masjid B',
            'tanggal' => now()->toDateString(),
            'status' => 'draft',
        ]);

        $this->actingAs($ketuaA)
            ->deleteJson('/kegiatan/galeri/'.$albumB->id)
            ->assertNotFound();

        $this->assertDatabaseHas('galeri_albums', ['id' => $albumB->id]);
    }

    #[DataProvider('modulHapusProvider')]
    public function test_pengurus_tidak_bisa_menghapus_data_masjid_lain(string $modul): void
    {
        [, $masjidB, $ketuaA] = $this->duaMasjid();
        $record = $this->buatMilikMasjid($modul, $masjidB);

        $this->actingAs($ketuaA)
            ->deleteJson($record['url'])
            ->assertNotFound();

        $this->assertDatabaseHas($record['table'], ['id' => $record['id']]);
    }

    public function test_pengurus_tidak_bisa_mengubah_kas_dan_jamaah_masjid_lain(): void
    {
        [, $masjidB, $ketuaA] = $this->duaMasjid();
        $kas = $this->buatMilikMasjid('kas', $masjidB);
        $jamaah = $this->buatMilikMasjid('jamaah', $masjidB);

        $this->actingAs($ketuaA)
            ->putJson($kas['url'], [
                'tanggal' => now()->toDateString(),
                'jenis' => 'Infaq',
                'tipe' => 'Pemasukan',
                'jumlah' => 1,
            ])
            ->assertNotFound();

        $this->actingAs($ketuaA)
            ->putJson($jamaah['url'], [
                'nama' => 'Diserobot A',
                'jenis_kelamin' => 'Laki-laki',
                'no_hp' => '0800',
            ])
            ->assertNotFound();

        $this->assertDatabaseHas('kas_transactions', ['id' => $kas['id'], 'keterangan' => 'Kas masjid B']);
        $this->assertDatabaseHas('jamaah', ['id' => $jamaah['id'], 'nama' => 'Jamaah masjid B']);
    }

    public function test_hapus_jabatan_hanya_mengenai_masjid_sendiri(): void
    {
        [$masjidA, $masjidB, $ketuaA] = $this->duaMasjid();

        Jabatan::create(['mosque_id' => $masjidA->id, 'nama' => 'Sekretaris YMBPK', 'urutan' => 1]);
        $jabatanB = Jabatan::create(['mosque_id' => $masjidB->id, 'nama' => 'Sekretaris YMBPK', 'urutan' => 1]);

        $this->actingAs($ketuaA)
            ->deleteJson('/kepengurusan/jabatan', ['nama' => 'Sekretaris YMBPK'])
            ->assertOk();

        $this->assertDatabaseMissing('jabatans', ['mosque_id' => $masjidA->id, 'nama' => 'Sekretaris YMBPK']);
        $this->assertDatabaseHas('jabatans', ['id' => $jabatanB->id]);
    }

    public function test_reset_jabatan_tidak_menghapus_struktur_masjid_lain(): void
    {
        [$masjidA, $masjidB, $ketuaA] = $this->duaMasjid();

        Jabatan::create(['mosque_id' => $masjidA->id, 'nama' => 'Ketua YMBPK', 'urutan' => 0]);
        $jabatanB = Jabatan::create(['mosque_id' => $masjidB->id, 'nama' => 'Pengurus Khusus B', 'urutan' => 0]);

        $this->actingAs($ketuaA)
            ->postJson('/kepengurusan/jabatan/reset')
            ->assertOk();

        $this->assertDatabaseHas('jabatans', ['id' => $jabatanB->id, 'nama' => 'Pengurus Khusus B']);
        $this->assertDatabaseHas('jabatans', ['mosque_id' => $masjidA->id, 'nama' => 'Sie Pendidikan']);
        $this->assertDatabaseMissing('jabatans', ['mosque_id' => $masjidB->id, 'nama' => 'Sie Pendidikan']);
    }

    public function test_pengurus_tidak_bisa_mengubah_relawan_kegiatan_masjid_lain(): void
    {
        [, $masjidB, $ketuaA] = $this->duaMasjid();
        $kegiatan = Kegiatan::create([
            'mosque_id' => $masjidB->id,
            'tanggal' => now()->toDateString(),
            'nama' => 'Kajian masjid B',
            'kategori' => 'Kajian',
            'status' => 'Akan Datang',
        ]);

        $this->actingAs($ketuaA)
            ->postJson('/relawan/'.$kegiatan->id, [
                'relawan' => [['nama' => 'Relawan A', 'telepon' => '0812']],
            ])
            ->assertNotFound();

        $this->assertDatabaseMissing('kegiatan_relawan', ['kegiatan_id' => $kegiatan->id]);
    }

    public function test_halaman_relawan_tidak_membocorkan_kegiatan_masjid_lain(): void
    {
        [$masjidA, $masjidB, $ketuaA] = $this->duaMasjid();

        Kegiatan::create([
            'mosque_id' => $masjidA->id,
            'tanggal' => now()->toDateString(),
            'nama' => 'Kegiatan masjid A',
            'kategori' => 'Kajian',
            'status' => 'Akan Datang',
        ]);
        Kegiatan::create([
            'mosque_id' => $masjidB->id,
            'tanggal' => now()->toDateString(),
            'nama' => 'ZZZ-ISOLASI-B-RELAWAN',
            'kategori' => 'Kajian',
            'status' => 'Akan Datang',
        ]);

        $this->actingAs($ketuaA)
            ->get('/relawan')
            ->assertOk()
            ->assertSee('Kegiatan masjid A')
            ->assertDontSee('ZZZ-ISOLASI-B-RELAWAN');
    }

    public function test_halaman_pengurus_tidak_membocorkan_data_masjid_lain(): void
    {
        [$masjidA, $masjidB, $ketuaA] = $this->duaMasjid();

        Jamaah::create([
            'mosque_id' => $masjidA->id,
            'nama' => 'Jamaah masjid A',
            'jenis_kelamin' => 'Laki-laki',
            'no_hp' => '0811',
            'status_jamaah' => 'Aktif',
        ]);
        Jamaah::create([
            'mosque_id' => $masjidB->id,
            'nama' => 'ZZZ-ISOLASI-B-JAMAAH',
            'jenis_kelamin' => 'Laki-laki',
            'no_hp' => '0812',
            'status_jamaah' => 'Aktif',
        ]);
        KasTransaction::create([
            'mosque_id' => $masjidB->id,
            'tanggal' => now()->toDateString(),
            'jenis' => 'Infaq',
            'keterangan' => 'ZZZ-ISOLASI-B-KAS',
            'pemasukan' => 100000,
            'pengeluaran' => 0,
        ]);
        Surat::create([
            'mosque_id' => $masjidB->id,
            'nomor' => 'B/999',
            'subjek' => 'ZZZ-ISOLASI-B-SURAT',
            'jenis' => 'Surat Undangan',
            'status' => 'Draft',
            'tanggal' => now()->toDateString(),
        ]);
        Inventaris::create([
            'mosque_id' => $masjidB->id,
            'nama' => 'ZZZ-ISOLASI-B-INVENTARIS',
            'kondisi' => 'Baik',
            'qty' => '1',
            'sumber' => 'Beli',
            'kode' => 'ISO-B-LEAK',
        ]);
        Kegiatan::create([
            'mosque_id' => $masjidB->id,
            'tanggal' => now()->toDateString(),
            'nama' => 'ZZZ-ISOLASI-B-AGENDA',
            'kategori' => 'Kajian',
            'status' => 'Akan Datang',
        ]);
        Donasi::create([
            'mosque_id' => $masjidB->id,
            'tanggal' => now()->toDateString(),
            'donatur' => 'ZZZ-ISOLASI-B-DONATUR',
            'kategori' => 'Infaq',
            'jenis' => 'Infaq Jumat',
            'tipe' => 'Uang',
            'nominal' => 50000,
            'status' => 'Berhasil',
        ]);
        User::create([
            'name' => 'ZZZ-ISOLASI-B-USER',
            'email' => 'isolasi-b@example.com',
            'password' => 'rahasia123',
            'role' => 'Petugas Zakat',
            'mosque_id' => $masjidB->id,
        ]);

        $this->actingAs($ketuaA);

        $this->get('/data-jamaah')->assertOk()->assertDontSee('ZZZ-ISOLASI-B-JAMAAH');
        $this->get('/keuangan/kas-masjid')->assertOk()->assertDontSee('ZZZ-ISOLASI-B-KAS');
        $this->get('/surat')->assertOk()->assertDontSee('ZZZ-ISOLASI-B-SURAT');
        $this->get('/inventaris')->assertOk()->assertDontSee('ZZZ-ISOLASI-B-INVENTARIS');
        $this->get('/kegiatan/agenda')->assertOk()->assertDontSee('ZZZ-ISOLASI-B-AGENDA');
        $this->get('/keuangan/infaq-sodaqoh')->assertOk()->assertDontSee('ZZZ-ISOLASI-B-DONATUR');
        $this->get('/pengaturan/user-management')->assertOk()->assertDontSee('ZZZ-ISOLASI-B-USER');
    }
}
