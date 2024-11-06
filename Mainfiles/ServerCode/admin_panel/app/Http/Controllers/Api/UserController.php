<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Users;
use App\Models\TV_Login;
use App\Models\General_Setting;
use Illuminate\Http\Request;
use Validator;
use Exception;

class UserController extends Controller
{
    private $folder = "user";

    public function registration(Request $request)
    {
        try {

            $validation = Validator::make(
                $request->all(),
                [
                    'type' => 'required|numeric',
                    'name' => 'required',
                    'email' => 'required|unique:user|email',
                    'password' => 'required',
                    'mobile' => 'required|numeric',
                ],
                [
                    'type.required' => __('api_msg.please_enter_required_fields'),
                    'email.required' => __('api_msg.please_enter_required_fields'),
                    'mobile.required' => __('api_msg.please_enter_required_fields'),
                ]
            );
            if ($validation->fails()) {

                $errors = $validation->errors()->first('type');
                $errors1 = $validation->errors()->first('email');
                $errors2 = $validation->errors()->first('mobile');
                $data['status'] = 400;
                if ($errors) {
                    $data['message'] = $errors;
                } elseif ($errors1) {
                    $data['message'] = $errors1;
                } elseif ($errors2) {
                    $data['message'] = $errors2;
                } else {
                    $data['message'] = __('api_msg.please_enter_required_fields');
                }
                return $data;
            }

            $type = $request->type;
            $name = $request->name;
            $email = $request->email;
            $password = $request->password;
            $mobile = $request->mobile;
            $email_array = explode('@', $email);

            $data = array(
                'name' => $name,
                'user_name' => user_name($email_array[0]),
                'mobile' => $mobile,
                'email' => $email,
                'password' => $password,
                'image' => "",
                'type' => $type,
                'status' => 1,
                'expiry_date' => "",
                'api_token' => "",
                'email_verify_token' => "",
                'is_email_verify' => "",
            );

            $user_id = Users::insertGetId($data);

            if (isset($user_id)) {

                $user_data = Users::where('id', $user_id)->first();

                imageNameToUrl(array($user_data), 'image', $this->folder);

                // Send Mail (Type = 1- Register Mail, 2 Transaction Mail)
                Send_Mail(1, $user_data->email);

                if ($user_data['expiry_date'] == null) {
                    $user_data['expiry_date'] = "";
                }

                return APIResponse(200, __('api_msg.User_registration_sucessfuly'), array($user_data));
            } else {
                return APIResponse(400, __('api_msg.data_not_save'));
            }
        } catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function login(Request $request) // 1- Facebook, 2- Google, 3- OTP, 4- Normal, 5- Apple
    {
        try {

            if ($request->type == 1 || $request->type == 2 || $request->type == 5) {

                $validation = Validator::make(
                    $request->all(),
                    [
                        'email' => 'required|email',
                    ],
                    [
                        'email.required' => __('api_msg.please_enter_required_fields'),
                    ]
                );
                if ($validation->fails()) {

                    $errors = $validation->errors()->first('name');
                    $errors1 = $validation->errors()->first('email');
                    $data['status'] = 400;
                    if ($errors) {
                        $data['message'] = $errors;
                    } elseif ($errors1) {
                        $data['message'] = $errors1;
                    }
                    return $data;
                }
            } elseif ($request->type == 3) {

                $validation = Validator::make(
                    $request->all(),
                    [
                        'mobile' => 'required|numeric',
                    ],
                    [
                        'mobile.required' => __('api_msg.please_enter_required_fields'),
                    ]
                );
                if ($validation->fails()) {

                    $errors = $validation->errors()->first('mobile');
                    $data['status'] = 400;
                    if ($errors) {
                        $data['message'] = $errors;
                    }
                    return $data;
                }
            } elseif ($request->type == 4) {

                $validation = Validator::make(
                    $request->all(),
                    [
                        'email' => 'required|email',
                        'password' => 'required',
                    ],
                    [
                        'email.required' => __('api_msg.please_enter_required_fields'),
                    ]
                );
                if ($validation->fails()) {

                    $errors = $validation->errors()->first('email');
                    $data['status'] = 400;
                    if ($errors) {
                        $data['message'] = $errors;
                    } else {
                        $data['message'] = __('api_msg.please_enter_required_fields');
                    }
                    return $data;
                }
            } else {
                $validation = Validator::make(
                    $request->all(),
                    [
                        'type' => 'required|numeric',
                    ],
                    [
                        'type.required' => __('api_msg.please_enter_required_fields'),
                    ]
                );
                if ($validation->fails()) {

                    $errors = $validation->errors()->first('type');
                    $data['status'] = 400;
                    if ($errors) {
                        $data['message'] = $errors;
                    }
                    return $data;
                }
            }

            $type = $request->type;
            $name = isset($request->name) ? $request->name : "";
            $email = isset($request->email) ? $request->email : "";
            $password = isset($request->password) ? $request->password : "";
            $mobile = isset($request->mobile) ? $request->mobile : "";

            if ($type == 1 || $type == 2 || $type == 5) {

                $data = Users::where('email', $email)->first();
                if (!empty($data)) {

                    // Image
                    imageNameToUrl(array($data), 'image', $this->folder);

                    if ($data['expiry_date'] == null) {
                        $data['expiry_date'] = "";
                    }

                    return APIResponse(200, __('api_msg.login_successfully'), array($data));
                } else {

                    $imageName = "";
                    if ($request->image != null) {
                        $org_name = $request->file('image');
                        $imageName = saveImage($org_name, $this->folder);
                    }
                    $email_array = explode('@', $email);

                    $data = array(
                        'name' => $name,
                        'user_name' => user_name($email_array[0]),
                        'mobile' => $mobile,
                        'email' => $email,
                        'password' => $password,
                        'image' => $imageName,
                        'type' => $type,
                        'status' => 1,
                        'expiry_date' => "",
                        'api_token' => "",
                        'email_verify_token' => "",
                        'is_email_verify' => "",
                    );
                    $user_id = Users::insertGetId($data);

                    if (isset($user_id)) {

                        $user_data = Users::where('id', $user_id)->first();

                        // Image
                        imageNameToUrl(array($user_data), 'image', $this->folder);

                        // Send Mail (Type = 1- Register Mail, 2 Transaction Mail)
                        if ($type == 2) {
                            Send_Mail(1, $user_data->email);
                        }

                        if ($user_data['expiry_date'] == null) {
                            $user_data['expiry_date'] = "";
                        }

                        return APIResponse(200, __('api_msg.login_successfully'), array($user_data));
                    } else {
                        return APIResponse(400, __('api_msg.data_not_save'));
                    }
                }
            } elseif ($type == 3) {

                $data = Users::where('mobile', $mobile)->first();
                if (!empty($data)) {

                    imageNameToUrl(array($data), 'image', $this->folder);

                    if ($data['expiry_date'] == null) {
                        $data['expiry_date'] = "";
                    }

                    return APIResponse(200, __('api_msg.login_successfully'), array($data));
                } else {

                    $data = array(
                        'name' => $name,
                        'user_name' => user_name($mobile),
                        'mobile' => $mobile,
                        'email' => $email,
                        'password' => $password,
                        'image' => "",
                        'type' => $type,
                        'status' => 1,
                        'expiry_date' => "",
                        'api_token' => "",
                        'email_verify_token' => "",
                        'is_email_verify' => "",
                    );
                    $user_id = Users::insertGetId($data);
                    if (isset($user_id)) {

                        $user_data = Users::where('id', $user_id)->first();

                        imageNameToUrl(array($user_data), 'image', $this->folder);

                        if ($user_data['expiry_date'] == null) {
                            $user_data['expiry_date'] = "";
                        }

                        return APIResponse(200, __('api_msg.login_successfully'), array($user_data));
                    } else {
                        return APIResponse(400, __('api_msg.data_not_save'));
                    }
                }
            } elseif ($type == 4) {

                $data = Users::where('email', $email)->where('password', $password)->first();
                if (!empty($data)) {

                    imageNameToUrl(array($data), 'image', $this->folder);

                    if ($data['expiry_date'] == null) {
                        $data['expiry_date'] = "";
                    }

                    return APIResponse(200, __('api_msg.login_successfully'), array($data));
                } else {
                    return APIResponse(400, __('api_msg.email_pass_worng'), []);
                }
            } else {
                return APIResponse(400, __('api_msg.change_type'));
            }
        } catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function get_profile(Request $request)
    {
        try {

            $validation = Validator::make(
                $request->all(),
                [
                    'id' => 'required|numeric',
                ],
                [
                    'id.required' => __('api_msg.please_enter_required_fields'),
                ]
            );
            if ($validation->fails()) {

                $errors = $validation->errors()->first('id');
                $data['status'] = 400;
                if ($errors) {
                    $data['message'] = $errors;
                }
                return $data;
            }

            $id = $request->id;
            $Data = Users::where('id', $id)->first();
            if (!empty($Data)) {

                imageNameToUrl(array($Data), 'image', $this->folder);

                if ($Data['expiry_date'] == null) {
                    $Data['expiry_date'] = "";
                }

                $Data['is_buy'] = IsBuyByUser($id);

                return APIResponse(200, __('api_msg.get_record_successfully'), array($Data));
            } else {
                return APIResponse(400, __('api_msg.data_not_found'));
            }
        } catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function image_upload(Request $request)
    {
        try {

            $validation = Validator::make(
                $request->all(),
                [
                    'id' => 'required|numeric',
                    'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
                ],
                [
                    'id.required' => __('api_msg.please_enter_required_fields'),
                    'image.required' => __('api_msg.please_enter_required_fields'),
                ]
            );
            if ($validation->fails()) {

                $errors = $validation->errors()->first('id');
                $errors1 = $validation->errors()->first('image');
                $data['status'] = 400;
                if ($errors) {
                    $data['message'] = $errors;
                } elseif ($errors1) {
                    $data['message'] = $errors1;
                }
                return $data;
            }

            $id = $request->id;
            $org_name = $request->file('image');

            $data = Users::where('id', $id)->first();
            if (!empty($data)) {

                deleteImageToFolder($this->folder, $data['image']);

                $data->image = saveImage($org_name, $this->folder);
                if ($data->save()) {
                    return APIResponse(200, __('api_msg.update_successfully', []));
                } else {
                    return APIResponse(400, __('api_msg.data_not_save'));
                }
            } else {
                return APIResponse(400, __('api_msg.data_not_found'));
            }
        } catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function update_profile(Request $request)
    {
        try {

            $validation = Validator::make(
                $request->all(),
                [
                    'id' => 'required|numeric',
                ],
                [
                    'id.required' => __('api_msg.please_enter_required_fields'),
                ]
            );
            if ($validation->fails()) {

                $errors = $validation->errors()->first('id');
                $data['status'] = 400;
                if ($errors) {
                    $data['message'] = $errors;
                }
                return $data;
            }

            $id = $request->id;
            $data = array();

            $User_Data = Users::where('id', $id)->first();
            if (!empty($User_Data)) {

                if (isset($request->name) && $request->name != '') {
                    $data['name'] = $request->name;
                }
                if (isset($request->email) && $request->email != '') {
                    $data['email'] = $request->email;
                }
                if (isset($request->mobile) && $request->mobile != '') {
                    $data['mobile'] = $request->mobile;
                }

                $User_Data->update($data);
                if (isset($User_Data)) {

                    imageNameToUrl(array($User_Data), 'image', $this->folder);

                    if ($User_Data['expiry_date'] == null) {
                        $User_Data['expiry_date'] = "";
                    }

                    return APIResponse(200, __('api_msg.update_profile_sucessfuly'), array($User_Data));
                }
            } else {
                return APIResponse(400, __('api_msg.User_id_worng'));
            }
        } catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    // TV Login
    public function get_tv_login_code(Request $request)
    {
        try {
            $validation = Validator::make(
                $request->all(),
                [
                    'device_token' => 'required',
                ],
                [
                    'device_token.required' => __('api_msg.please_enter_required_fields'),
                ]
            );
            if ($validation->fails()) {

                $errors = $validation->errors()->first('device_token');
                $data['status'] = 400;
                if ($errors) {
                    $data['message'] = $errors;
                }
                return $data;
            }

            $check = TV_Login::where('device_token', $request->device_token)->first();

            if (isset($check)) {

                if ($check->status == 1 && $check->user_id != 0) {

                    $check->device_token = $request->device_token;
                    $check->unique_code = TV_Login_Code();
                    $check->status = 0;
                    $check->user_id = 0;
                    $check->update();
                }
                return APIResponse(200, __('api_msg.get_record_successfully'), array($check));
            } else {

                $insert = new TV_Login();
                $insert->unique_code = TV_Login_Code();
                $insert->device_token = $request->device_token;
                $insert->status = 0;
                $insert->user_id = 0;

                if ($insert->save()) {
                    return APIResponse(200, __('api_msg.get_record_successfully'), array($insert));
                } else {
                    return APIResponse(400, __('api_msg.data_not_save'));
                }
            }
        } catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }

    public function tv_login(Request $request)
    {
        try {
            $validation = Validator::make(
                $request->all(),
                [
                    'user_id' => 'required|numeric',
                    'unique_code' => 'required',
                ],
                [
                    'user_id.required' => __('api_msg.please_enter_required_fields'),
                    'unique_code.required' => __('api_msg.please_enter_required_fields'),
                ]
            );
            if ($validation->fails()) {

                $errors = $validation->errors()->first('user_id');
                $errors1 = $validation->errors()->first('unique_code');
                $data['status'] = 400;
                if ($errors) {
                    $data['message'] = $errors;
                } else if ($errors1) {
                    $data['message'] = $errors1;
                }
                return $data;
            }

            $check = TV_Login::where('unique_code', $request->unique_code)->where('status', 0)->where('user_id', 0)->first();

            if (isset($check)) {

                $check->status = 1;
                $check->user_id = $request->user_id;

                if ($check->update()) {

                    $toUser[] = $check->device_token;

                    $data = array(
                        'id' => $check->id,
                        'user_id' => $check->user_id,
                        'device_token' => $check->device_token,
                        'unique_code' => $check->unique_code,
                        'status' => $check->status,
                    );

                    $noty = General_Setting::where('key', 'onesignal_apid')->orWhere('key', 'onesignal_rest_key')->get();
                    $notification = [];
                    foreach ($noty as $row) {
                        $notification[$row->key] = $row->value;
                    }
                    $ONESIGNAL_APP_ID = $notification['onesignal_apid'];
                    $ONESIGNAL_REST_KEY = $notification['onesignal_rest_key'];

                    $title = "Login SuccessFully.";

                    $fields = array(
                        'app_id' => $ONESIGNAL_APP_ID,
                        'include_android_reg_ids' => $toUser,
                        "isAndroid" => true,
                        "channel_for_external_user_ids" => "push",
                        'headings' => array("en" => $title),
                        'contents' => array("en" => $title),
                        'data' => $data,
                    );

                    $fields = json_encode($fields);

                    $ch = curl_init();

                    curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        'Content-Type: application/json; charset=utf-8',
                        'Authorization: Basic ' . $ONESIGNAL_REST_KEY
                    ));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                    curl_setopt($ch, CURLOPT_HEADER, FALSE);
                    curl_setopt($ch, CURLOPT_POST, TRUE);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

                    $response = curl_exec($ch);
                    // dd($response);
                    curl_close($ch);

                    $data = Users::where('id', $check->user_id)->first();
                    if (isset($data)) {

                        // Image
                        imageNameToUrl(array($data), 'image', $this->folder);

                        unset($data['password']);

                        if ($data['expiry_date'] == null) {
                            $data['expiry_date'] = "";
                        }
                        return APIResponse(200, __('api_msg.get_record_successfully'), array($data));
                    } else {
                        return APIResponse(400, __('api_msg.User_id_worng'));
                    }
                } else {
                }
            } else {

                return APIResponse(400, "Code Is Wrong.",);
            }
        } catch (Exception $e) {
            return response()->json(array('status' => 400, 'errors' => $e->getMessage()));
        }
    }
}
