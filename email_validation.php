<?php function email_validation(string $email){
    return preg_match('/[a-z._0-9]+[@]{1}[a-z._0-9]+[.]{1}[a-z0-9]{2,}/i',$email) === 1;
}
function main(){
    global $argc;
    global $argv;
    if ($argc == 2){
        $email = $argv[1];
        if (email_validation($email)){
            echo "Email '$email' is valid!";
        }
        else echo "Email '$email' is not valid!";
    }
    else{
        echo 'Please give email address.';
    }
}