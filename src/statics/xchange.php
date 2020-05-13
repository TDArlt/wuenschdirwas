<?php

    // Allow cross origin access
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: *");
    header("Access-Control-Allow-Methods: GET, POST");

    // Define output format
    header('Content-Type: application/json');

    // Get users
    $userInfo = json_decode(file_get_contents('data/user.json'));

    //Authenticate

    /*
    if (!isset($_SERVER['PHP_AUTH_USER']))
    {
        header('WWW-Authenticate: Basic realm="API"');
        header('HTTP/1.0 401 Unauthorized');
        $_JSON = ['error' => 'You need to provide credentials in http header using basic auth. See also: https://de.wikipedia.org/wiki/HTTP-Authentifizierung'];
        
        print json_encode($_JSON);

        exit;
    }


    //$_SERVER['PHP_AUTH_USER']
    //$_SERVER['PHP_AUTH_PW']

    */

    if (!isset($_POST['type']))
    {
        $postInfo = json_decode(file_get_contents('php://input'), true);
    } else {
        $postInfo = $_POST;
    }

    if (isset($postInfo['type']))
    {
        if ($postInfo['type'] == 'login')
        {
            if ($postInfo['password'] == $userInfo->admin->password)
            {
                $_OUTPUT = ['success' => true, 'token' => $userInfo->admin->token];
            } else if ($postInfo['password'] == $userInfo->guest->password)
            {
                $_OUTPUT = ['success' => true, 'token' => $userInfo->guest->token];
            } else
            {
                $_OUTPUT = ['success' => false];
            }
        }

        // Secured area

        if (isset($postInfo['token']) &&
            ($postInfo['token'] == $userInfo->admin->token ||
            $postInfo['token'] == $userInfo->guest->token
            ))
        {
            $jsonList = json_decode(file_get_contents('data/list.json'), true);


            if ($postInfo['type'] == 'list')
            {
                // Return current list
                $_OUTPUT = [];

                // If we are not admin, we need to skip invisible and fulfilled objects
                if ($postInfo['token'] == $userInfo->guest->token)
                {
                    foreach ($jsonList as $element)
                    {
                        if ($element['visible'] == true && $element['fulfilled'] == false)
                        {
                            $_OUTPUT[] = $element;
                        }
                    }
                } else
                {
                    $_OUTPUT = $jsonList;
                }
            }


            if ($postInfo['type'] == 'update')
            {
                $elementPosition = -1;
                for ($i=0; $i < count($jsonList); $i++)
                { 
                    if ($jsonList[$i] == $postInfo['id'])
                    {
                        $elementPosition = $i;
                    }
                }

                if ($postInfo['token'] == $userInfo->admin->token)
                {
                    $element = [
                        'id' => $postInfo['id'],
                        'name' => $postInfo['name'],
                        'description' => $postInfo['description'],
                        'links' => json_decode($postInfo['links'], true),
                        'visible' => $postInfo['visible'],
                        'reserved' => $postInfo['reserved'],
                        'fulfilled' => $postInfo['fulfilled'],
                        'position' => $postInfo['position']
                    ];

                    if ($elementPosition > -1)
                    {
                        $jsonList[$elementPosition] = $element;
                    } else
                    {
                        $jsonList[] = $element;
                    }
                } else
                {
                    if ($elementPosition > -1)
                    {
                        $jsonList[$elementPosition]['fulfilled'] = $postInfo['fulfilled'];
                        $jsonList[$elementPosition]['reserved'] = $postInfo['reserved'];
                    }
                }

                $listFile = fopen("data/list.json", "w");
                fwrite($listFile, json_encode($jsonList));
                fclose($listFile);

            }


            if ($postInfo['type'] == 'delete' && $postInfo['token'] == $userInfo->admin->token)
            {
                $elementPosition = -1;
                for ($i=0; $i < count($jsonList); $i++)
                { 
                    if ($jsonList[$i] == $postInfo['id'])
                    {
                        $elementPosition = $i;
                    }
                }

                if ($elementPosition > -1)
                {
                    unset($jsonList[$elementPosition]);
                    
                    $listFile = fopen("data/list.json", "w");
                    fwrite($listFile, json_encode($jsonList));
                    fclose($listFile);
                }
            }



            if ($postInfo['type'] == 'mail')
            {
                $subject = $postInfo['subject'];
                $message = $postInfo['message'];
                $to = 'ca.ch@cf-studios.com';
                if (isset($postInfo['to']))
                {
                    $to = $postInfo['to'];
                }
                
                $header = 'From: ca.ch@cf-studios.com' . "\r\n" .
                    'Reply-To: ca.ch@cf-studios.com' . "\r\n" .
                    'X-Mailer: PHP/' . phpversion();

                $success = @mail ($to, $subject, $message, $header);

                $_OUTPUT = ['success' => $success];
            }

        }

    }


    if (isset($_OUTPUT))
    {
        echo json_encode($_OUTPUT);
    } else
    {
        echo '[]';
    }