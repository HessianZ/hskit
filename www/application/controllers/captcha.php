<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Captcha extends HS_Controller {

    public function guestbook() {
        $this->load->helper("captcha");

        $word = mt_rand(0, 9) . mt_rand(0, 9) . mt_rand(0, 9) . mt_rand(0, 9);

        $cap_settings = array(
            'img_path' => FCPATH . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR . "captcha" . DIRECTORY_SEPARATOR,
            'img_url' => '/images/captcha/',
            'img_width' => 80,
            'img_height' => 26,
            'expiration' => 3600,
            'word' => $word
        );

        $cap = create_captcha($cap_settings);

        $this->load->library('session');
        $this->session->set_userdata('guest_captcha', $cap['word']);

        echo $cap['image'];
    }

}
