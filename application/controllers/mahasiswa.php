<?php
class Mahasiswa extends CI_Controller
{
    public function index()
    {
        $data['mahasiswa'] = $this->maha->tampil_data()->result();

        $this->load->view('templates/sidebar');
        $this->load->view('templates/header');
        $this->load->view('templates/footer');
        $this->load->view('mahasiswa', $data);
    }


    public function tambah_aksi()
    {
        $nama = $this->input->post('nama');
        $nim = $this->input->post('nim');
        $tgl_lahir = $this->input->post('tgl_lahir');
        $jurusan = $this->input->post('jurusan');
        $alamat = $this->input->post('alamat');
        $email = $this->input->post('email');
        $no_tlp = $this->input->post('no_tlp');
        $foto = $_FILES['foto'];
        if ($foto = '') {
        } else {
            //folder untuk mengupload foto
            $config['upload_path'] = './assets/poto';
            //file yg diizinkan untuk diupload
            $config['allowed_types'] = 'jpg|png|gif';

            $this->load->library('upload', $config);
            if (!$this->upload->do_upload('foto')) {
                echo "Upload gagal ";
                die();
            } else {
                $foto = $this->upload->data('file_name');
            }
        }

        $data = array(
            'nama' => $nama,
            'nim' => $nim,
            'tgl_lahir' => $tgl_lahir,
            'jurusan' => $jurusan,
            'alamat' => $alamat,
            'email' => $email,
            'no_tlp' => $no_tlp,
            'foto' => $foto

        );

        $this->maha->input_data($data, 'tb_mahasiswa');
        redirect('mahasiswa/index');
    }

    public function hapus($id)
    {

        $where = array('id' => $id);
        $this->maha->hapus_data($where, 'tb_mahasiswa');
        redirect('mahasiswa/index');
    }

    public function edit($id)
    {
        $where = array('id' => $id);
        $data['mahasiswa'] = $this->maha->edit_data($where, 'tb_mahasiswa')->result();

        $this->load->view('templates/sidebar');
        $this->load->view('templates/header');
        $this->load->view('templates/footer');
        $this->load->view('edit', $data);
    }

    public function update()
    {
        $id = $this->input->post('id');
        $nama = $this->input->post('nama');
        $nim = $this->input->post('nim');
        $tgl_lahir = $this->input->post('tgl_lahir');
        $jurusan = $this->input->post('jurusan');

        $alamat = $this->input->post('alamat');
        $email = $this->input->post('email');
        $no_tlp = $this->input->post('no_tlp');


        $data = array(
            'nama' => $nama,
            'nim' => $nim,
            'tgl_lahir' => $tgl_lahir,
            'jurusan' => $jurusan,

            'alamat' => $alamat,
            'email' => $email,
            'no_tlp' => $no_tlp


        );
        $where = array(
            'id' => $id
        );
        $this->maha->update_data($where, $data, 'tb_mahasiswa');
        redirect('mahasiswa/index');
    }

    public function detail($id)
    {
        $this->load->model('maha');
        $detail = $this->maha->detail_data($id);
        $data['detail'] = $detail;

        $this->load->view('templates/sidebar');
        $this->load->view('templates/header');
        $this->load->view('templates/footer');
        $this->load->view('detail', $data);
    }

    public function print()
    {
        $data['mahasiswa'] = $this->maha->tampil_data('tb_mahasiswa')->result();
        $this->load->view('print_mahasiswa', $data);
    }

    public function pdf()
    {
        $this->load->library('dompdf_gen');

        $data['mahasiswa'] = $this->maha->tampil_data('tb_mahasiswa')->result();

        $this->load->view('laporan', $data);

        $paper_size = 'A4';
        $orientation = 'landscape';
        $html = $this->output->get_output();
        $this->dompdf->set_paper($paper_size, $orientation);

        $this->dompdf->load_html($html);
        $this->dompdf->render();

        $this->dompdf->stream("laporan.pdf", array('Attachment' => 0));
    }

    public function excel()
    {
        $data['mahasiswa'] = $this->maha->tampil_data('tb_mahasiswa')->result();


        require(APPPATH . '/PHPExcel-1.8/Classes/PHPExcel.php');

        require(APPPATH . '/PHPExcel-1.8/Classes/PHPExcel/Writer/Excel2007.php');

        $obj = new PHPExcel();

        $obj->getProperties()->setCreator("Latihan Excel");
        $obj->getProperties()->setLastModifiedBy("Latihan Excel");
        $obj->getProperties()->setTitle("Daftar Mahasiswa");

        $obj->setActiveSheetIndex(0);

        $obj->getActiveSheet()->setCellValue('A1', 'No');
        $obj->getActiveSheet()->setCellValue('B1', 'Nama Mahasiswa');
        $obj->getActiveSheet()->setCellValue('C1', 'Name');
        $obj->getActiveSheet()->setCellValue('D1', 'Tanggal Lahir');
        $obj->getActiveSheet()->setCellValue('E1', 'Jurusan');
        $obj->getActiveSheet()->setCellValue('F1', 'ALamat');
        $obj->getActiveSheet()->setCellValue('G1', 'Email');
        $obj->getActiveSheet()->setCellValue('H1', 'No. tlp');

        $baris = 2;
        $no = 1;

        foreach ($data['mahasiswa'] as $mhs) {
            $obj->getActiveSheet()->setCellValue('A' . $baris, $no++);
            $obj->getActiveSheet()->setCellValue('B' . $baris, $mhs->nama);
            $obj->getActiveSheet()->setCellValue('C' . $baris, $mhs->nim);
            $obj->getActiveSheet()->setCellValue('D' . $baris, $mhs->tgl_lahir);
            $obj->getActiveSheet()->setCellValue('E' . $baris, $mhs->jurusan);
            $obj->getActiveSheet()->setCellValue('F' . $baris, $mhs->alamat);
            $obj->getActiveSheet()->setCellValue('G' . $baris, $mhs->email);
            $obj->getActiveSheet()->setCellValue('H' . $baris, $mhs->no_tlp);

            $baris++;
        }

        $filename = "Data Mahasiswa" . '.xlsx';

        $obj->getActiveSheet()->setTitle('Data Mahasiswa');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = PHPExcel_IOFactory::createWriter($obj, 'Excel2007');
        $writer->save('php://output');
        exit;
    }

    public function search()
    {
        $keyword = $this->input->post('keyword');
        $data['mahasiswa'] = $this->maha->get_keyword($keyword);


        $this->load->view('templates/sidebar');
        $this->load->view('templates/header');
        $this->load->view('templates/footer');
        $this->load->view('mahasiswa', $data);
    }
}
