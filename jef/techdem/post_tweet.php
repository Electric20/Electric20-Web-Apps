<?php
/**
* post_tweet.php
* Example of posting a tweet with OAuth
* Latest copy of this code:
* http://140dev.com/twitter-api-programming-tutorials/hello-twitter-oauth-php/
* @author Adam Green <140dev@gmail.com>
* @license GNU Public License
*/
include './config.php';

   echo ('put in your parameters');

//        echo $_POST["key"];
//           echo $_POST["watts"];
//    echo $_GET["test"];

       if ($_POST["key"] == "orchid2011") {
               $tweet_text = get_tweet_text($_POST["watts"]);
       echo 'tweet: '.$tweet_text;
            print "Posting...\n";
       $result = post_tweet($tweet_text,$consumer_key,$consumer_secret,
$oauth_token,$oauth_token_secret);
       print "Response code: " . $result . "\n";
   }


function post_tweet($tweet_text,$consumer_key,$consumer_secret,$oauth_token,$oauth_token_secret) {

 // Use Matt Harris' OAuth library to make the connection
 // This lives at: https://github.com/themattharris/tmhOAuth
 require_once('./tmhoauth.php');

 // Set the authorization values
 // In keeping with the OAuth tradition of maximum confusion,
 // the names of some of these values are different from the Twitter Dev interface
 // user_token is called Access Token on the Dev site
 // user_secret is called Access Token Secret on the Dev site
 // The values here have asterisks to hide the true contents
 // You need to use the actual values from your Twitter app
 $connection = new tmhOAuth(array(
   'consumer_key' => $consumer_key,
   'consumer_secret' => $consumer_secret,
   'user_token' => $oauth_token,
   'user_secret' => $oauth_token_secret
 ));

 // Make the API cal
// $connection->request('GET',
// $connection->url('1/account/rate_limit_status'));
//return $connection->response['response'];

 $connection->request('POST',
   $connection->url('1/statuses/update'),
   array('status' => $tweet_text));

  return $connection->response['code'];
}

function get_tweet_text($watts) {

   echo 'watts: ';
   echo ($watts);

   $min = 0;
   $max = 7;
   $text ='';

   $rand_num =  rand($min, $max);

   echo 'random number: ';
   echo ($rand_num).'\n';
 switch ($rand_num) {
               case 0:
                       $text = 'I\'m the Technology Demonstrator coffee machine, I just made a brew and used '.$watts.' Watts.';                     break;
               case 1:
                       $text = 'Just the Technology Demonstrator coffee machine squeezing another one out. '.$watts.' #watts';
                       break;
               case 2:
                       $text = 'Yum, lovely a mug of jo for someone in the Technology Demonstrator. '.$watts.' #watts';
                       break;
               case 3:
                       $text = 'There you go, another mug of black gold for somebody. Did you know that I used '.$watts.' Watts to do that.';
                       break;
               case 4:
                       $text = 'I\'m much more than a coffee machine, I can do tea too. This time I used '.$watts.' Watts';
                       break;
               case 5:
                       $text = 'I just used '.$watts .' watts to make your coffee.';
                       break;
               case 6:
                       $text = 'My buttons have just been pressed - and then I made coffee using '.$watts.' Watts.';
                       break;
               case 7:
                       $text = 'You guys in the Technoogy Demonstrator are thirsty today! another '.$watts.' Watts used to keep you awake/hydrated.';
                       break;
               case 8:
                       $text = 'Hey followers. Tell me @TechDemEnergy what to say when making a coffee. '.$watts.' #watts #uncreativebots';
                       break;
       }

       return $text;
}

?>

