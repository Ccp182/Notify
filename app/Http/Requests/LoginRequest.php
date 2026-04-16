<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'username' => ['required', 'string'],
            'password' => ['required'],
            
        ];
    }

    public function getCredentials(){
        $remenber = $this->input('remember_me') ?true:false;
        return [
            'username'=> $this->get('username'),
            'password'=> $this->get('password'),
            'app'=> $this->get('app'),
            'remember_me'=> $remenber,
            'country'=>$this->get('country'),
            'appNotify'=>$this->get('appNotify'),
            
        ];
    }
}
