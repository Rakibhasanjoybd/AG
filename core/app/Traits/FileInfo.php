<?php

namespace App\Traits;

trait FileInfo
{

    /*
    |--------------------------------------------------------------------------
    | File Information
    |--------------------------------------------------------------------------
    |
    | This trait basically contain the path of files and size of images.
    | All information are stored as an array. Developer will be able to access
    | this info as method and property using FileManager class.
    |
    */

    public function fileInfo(){
        $data['withdrawVerify'] = [
            'path'=>'assets/images/verify/withdraw'
        ];
        $data['depositVerify'] = [
            'path'      =>'assets/images/verify/deposit'
        ];
        $data['verify'] = [
            'path'      =>'assets/verify'
        ];
        $data['default'] = [
            'path'      => 'assets/images/default.png',
        ];
        $data['withdrawMethod'] = [
            'path'      => 'assets/images/withdraw/method',
            'size'      => '800x800',
        ];
        $data['ticket'] = [
            'path'      => 'assets/support',
        ];
        $data['logoIcon'] = [
            'path'      => 'assets/images/logoIcon',
        ];
        $data['favicon'] = [
            'size'      => '128x128',
        ];
        $data['extensions'] = [
            'path'      => 'assets/images/extensions',
            'size'      => '36x36',
        ];
        $data['seo'] = [
            'path'      => 'assets/images/seo',
            'size'      => '1180x600',
        ];
        $data['ptc'] = [
            'path'      => 'assets/images/ptc',
        ];
        $data['userProfile'] = [
            'path'      =>'assets/images/user/profile',
            'size'      =>'350x300',
        ];
        $data['adminProfile'] = [
            'path'      =>'assets/admin/images/profile',
            'size'      =>'400x400',
        ];
        $data['vipTask'] = [
            'path'      => 'assets/images/vip_tasks',
            'size'      => '400x300',
        ];
        $data['spotlight'] = [
            'path'      => 'assets/images/spotlights',
            'size'      => '800x400',
        ];
        $data['tutorial'] = [
            'path'      => 'assets/images/tutorials',
            'size'      => '400x225',
        ];
        $data['audioPlayer'] = [
            'path'      => 'assets/images/audio',
            'size'      => '100x100',
        ];
        $data['walletHeader'] = [
            'path' => 'assets/images/wallet-header',
            'size' => '600x600'
        ];
        // Red Pack (Free Tasks)
        $data['redPack'] = [
            'path' => 'assets/images/red_packs',
            'size' => '400x400',
        ];
        $data['redPackTask'] = [
            'path' => 'assets/images/red_pack_tasks',
            'size' => '400x300',
        ];
        $data['brand'] = [
            'path' => 'assets/images/brands',
            'size' => '300x100',
        ];
        return $data;
	}

}
