<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Home extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->helper(array('form', 'url'));
	}

    function index()
    {
        //echo twig_render('home_index.html');
        //the index page shows the default page for all users
        echo twig_render('app_index.html');
    }
	function img()
	{
        echo twig_render('home_index.html');
    }


	function track()
	{
        //html page to display traking info.
        // $trackingid = $_POST['trackingID'];
        // $data ['trackid'] = $trackingid;
        // echo twig_render('app_trackItems.html',$data);
        echo twig_render('app_trackItems.html');
	}

    function upload()
    {
        $path                    = 'assets/';
        $config['upload_path']   = $path;
        $config['allowed_types'] = 'gif|jpg|png';
        $config['max_size']      = '2048';
        $config['max_width']     = '1170';
        $config['max_height']    = '786';
        $config['encrypt_name']  = TRUE;

        $this->load->library('upload', $config);
        $images = array();

        foreach($_FILES as $field => $file)
        {
            if ($this->upload->do_upload($field))
            {
                $image = array('upload_data' => $this->upload->data());
                array_push($images, $image['upload_data']['file_name']);
            }
            else
            {
                $error = array('error' => $this->upload->display_errors());
                Kint::dump($error);
            }
        }
        $data['images'] = $images;
        echo twig_render('images_view.html', $data);
    }

    function crop()
    {
        $url = parse_url($_SERVER['REQUEST_URI']);
        $data['image_name'] = str_replace('img=', '', $url['query']);
        echo twig_render('crop_image.html', $data);
    }

    function do_crop()
    {
        $x = $_POST['x'];
        $y = $_POST['y'];
        $w = $_POST['w'];
        $h = $_POST['h'];
        $image_name = $_POST['image_name'];

        $targ_w = 800;
        $targ_h = 620;
        $jpeg_quality = 90;

        $src = './assets/'.$image_name;
        $img_r = imagecreatefromjpeg($src);
        $dst_r = ImageCreateTrueColor( $targ_w, $targ_h );

        imagecopyresampled($dst_r, $img_r, 0, 0, $x, $y, 
                        $targ_w, $targ_h, $w, $h);

        imagejpeg($dst_r, $src, $jpeg_quality);
        imagedestroy($dst_r);

        //$scaled_image = imagejpeg($dst_r, null, $jpeg_quality);

        //$config['upload_path']   = './assets/';
        //$this->load->library('upload', $config);

        //if ($this->upload->do_upload($scaled_image))
        //{
            //$image = array('upload_data' => $this->upload->data());
        //}
        //else
        //{
            //$error = array('error' => $this->upload->display_errors());
            //Kint::dump($error);
        //}
        
        exit;
    }
}
