<?php 
 /**
 * This file is a collection of usefull code snippets that took some finding to do.
 */

 /**
 * This function makes use of the custom pagination in laravel
 */
   public function index(Request $request)
    { 
        
        $data['pageName'] = 'message-board';
        $data['pageTitle'] = 'Message Board';
        $messages = Messages::where([
                ['to_user_id', '=', $request->session()->get('user.user_id')],
                ['message_id', '=', 0],
            ])
            ->orWhere([
                ['from_user_id', $request->session()->get('user.user_id')],
                ['message_id', '=', 0]
            ])
            ->where('deleted_for' , '!=', $request->session()->get('user.user_id')) 

            ->orderBy('id', 'desc')->get()->toArray();   

        foreach ( $messages as $key => $message ) {
            //

            // Associating from name with each of the message for the current user
            $users = Users::where('id', $message['from_user_id'])->get()->first();
            if($users && $users->id != Session::get('user.user_id')) $messages[$key]['fromName'] = $users->name . ' ' . $users->surname;
            elseif ($users && $users->id == Session::get('user.user_id')) $messages[$key]['fromName'] = 'You';
            else $messages[$key]['fromName'] = '';
            //

            //Setting status of messages only if the message was sent to the current user
                if($message['to_user_id'] == Session::get('user.user_id')) {
                    $messages[$key]['status'] = $message['status'];
                } else {
                    $messages[$key]['status'] = 'read';
                }
            //

            //Comma sparated value of ids for related messages
            $replyarr = [];
            $mainmsg = Messages::where('id', $message['id'])->get()->toArray()[0];
            $messages[$key]['ids'] = $message['id'];
            $message_id = $message['id'];
            do {
                $message_reply = Messages::where('message_id', $message_id)->get();
                if(!$message_reply->isEmpty()) { 
                    $messages[$key]['ids'] .= ','.$message_reply[0]->id;
                    $message_id = $message_reply[0]->id;
                }

            } while (!$message_reply->isEmpty()); 
            // 
            
            //Removing the messages that are set as deleted for current user
            $message_deleted_for = explode(',', $message['deleted_for']);
            if(in_array($request->session()->get('user.user_id'), $message_deleted_for)) unset($messages[$key]);

        }


        //Pagination
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;
        $currentItems = array_slice($messages, $perPage * ($currentPage - 1), $perPage);
        $paginator = new LengthAwarePaginator($currentItems, count($messages), $perPage, $currentPage, ['path'  => $request->url(),'query' => $request->query()]);
        if(count($messages) <= $perPage) {
            $data['hasPagination'] = false;
        } else {
            $data['hasPagination'] = true;
        }
         
        $data['messages'] = $paginator ;  
        $data['init'] = 'messageHandler.init()';
        $data['moduleJs'] = 'my-account/messageHandler';
        return view('pages.common.messages', $data);
    } 


    /**
    * This function creates subdomains dynamically
    */
    public function createSubDomain() {
	    $subDomain = $userName;
		$rootDomain = 'doormat.webmasterindia.net';

		$buildRequest = "/frontend/paper_lantern/subdomain/doadddomain.html?rootdomain=" . $rootDomain . "&domain=" . $subDomain . "&dir=public_html/user";

		$openSocket = fsockopen('localhost',2082);
		if(!$openSocket) {
		    return "Socket error";
		    exit();
		}

		$authString = 'doormat' . ":" . 'A@2Nay;SuVgQ';
		$authPass = base64_encode($authString);
		$buildHeaders  = "GET " . $buildRequest ."\r\n";
		$buildHeaders .= "HTTP/1.0\r\n";
		$buildHeaders .= "Host:localhost\r\n";
		$buildHeaders .= "Authorization: Basic " . $authPass . "\r\n";
		$buildHeaders .= "\r\n";

		fputs($openSocket, $buildHeaders);
		while(!feof($openSocket)) {
		    fgets($openSocket,128);
		}
		fclose($openSocket);

		$newDomain = "http://" . $subDomain . "." . $rootDomain . "/";
    }


    /**
    * This function deletes the subdomains dynamically
    */
    public function deleteSubdomain($subdomain) {
    	$disallowed = array('http://', 'https://');
		//Removing protocols part from the subdomains links
		foreach($disallowed as $d) {
		    if(strpos($subdomain, $d) === 0) {
		       $subdomain = rtrim(str_replace($d, '', $subdomain), '/');
		    }
		}


		$rootDomain = 'doormat.webmasterindia.net';

		$buildRequest = "/frontend/paper_lantern/subdomain/dodeldomain.html?domain=".$subdomain."_".$rootDomain;

		$openSocket = fsockopen('localhost',2082);
		if(!$openSocket) {
		    return "Socket error";
		    exit();
		}
		$username = $this->decode('cnnql`s');
		$password = $this->decode('@?1M`x:RtUfP'); 
		$authString = $username . ":" . $password;
		$authPass = base64_encode($authString);
		$buildHeaders  = "GET " . $buildRequest ."\r\n";
		$buildHeaders .= "HTTP/1.0\r\n";
		$buildHeaders .= "Host:localhost\r\n";
		$buildHeaders .= "Authorization: Basic " . $authPass . "\r\n";
		$buildHeaders .= "\r\n";

		fputs($openSocket, $buildHeaders);
		while(!feof($openSocket)) {
		    fgets($openSocket,128);
		}
		fclose($openSocket);

		return true;
    }
