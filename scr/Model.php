<?php

/**
 * This function determines if a valid GET request has been made, calls
 * a function to execute it then returns the results
 * 
 * args: $pdo - a connection to the DBMS
 *       $resource - the resource the user entered into the URI
 * 
 * returns: $data_status_arr - an array containing the content of the api request and the 
 *          status code of it
 */
function get_data($pdo,$resource) {
    // checks how many items are in resources and if they are of a valid form
    if (count($resource) == 1) {
        // calls a function to carry out the query on the DBMS
        $data_status_arr = get_req_no_param($pdo,$resource);
        return $data_status_arr;
    }
    else if (count($resource) == 2) {
        if ($resource[0] == "teams") {
            // calls a function to carry out the query on the DBMS
            $data_status_arr = get_req_all_players($pdo,$resource);
            return $data_status_arr;
        }
    }
    else if (count($resource) == 4) {
        if ($resource[0] == "teams" && $resource[2] == "players") {
            // calls a function to carry out the query on the DBMS
            $data_status_arr = get_single_player($pdo,$resource);
            return $data_status_arr;
        }
    }
    
}
/**
 * Gets all information on all teams sorted by team name
 * 
 *  * args: $pdo - a connection to the DBMS
 *       $resource - the resource the user entered into the URI
 * 
 * returns: $data_status_arr - an array containing the content of the api request and the 
 *          status code of it
 */
function get_req_no_param($pdo,$resource) {
    // checks the api request is in a valid form
    if ($resource[0] == "teams") {
        $table_name = "Teams";
        $order_param = "teamName";
    }
    // sends empty data and a status 400 if api rquest is in invalud form 
    else {
        $data_status_arr = [json_encode([]),json_encode(array("status" => "400"))];
        return $data_status_arr;
    }
    // Creates a prepared sql squery to information on all teams orderd by team name
    $query = "SELECT * from $table_name ORDER BY :order_param ";
    $pre_query = $pdo -> prepare($query);
    $pre_query->bindParam(':order_param', $order_param);
    $pre_query->execute();
    $results = $pre_query->fetchAll();
    $data = json_encode($results);
    /*
     * The rowCount function I learnt from this scource:
     * PHP Documentation "PDOStatement::rowCount"
     * URL: https://www.php.net/manual/en/pdostatement.rowcount.php#:~:text=PDOStatement%3A%3ArowCount()%20returns,be%20different%20for%20each%20driver.
     * This function is used to check if an sql query has affected any rows in the DBMS
     */
    $count = $pre_query -> rowCount();
    // If the api request is in a valid form but there are no teams in the data base
    // an empty string and a 204 error is sent
    if (!$results) {
        $data_status_arr = [$data,json_encode(array("status" => "204"))];
        return $data_status_arr;
    }
    else {
        // A function is called to created HATEOAS links for the http output body
        $data = hateoas_links($data,$table_name);
        // removes escape slashes from HATEOAS links
        $data = json_encode($data, JSON_UNESCAPED_SLASHES);
        // retunrs the json encoded data with a 200 status code
        $data_status_arr = [$data,json_encode(array("status" => "200"))];
        return $data_status_arr;
    } 
}
/**
 * Gets all infomation of every player on a specific team
 * 
 * args: $pdo - a connection to the DBMS
 *       $resource - the resource the user entered into the URI
 * 
 * returns: $data_status_arr - an array containing the content of the api request and the 
 *          status code of it
 */
function get_req_all_players($pdo,$resource) {
    // checks the api request is in a valid form
    if ($resource[0] == "teams") {
        $table_name = "Players";
        $parameter = "playsFor";
        }
    // sends empty data and a status 400 if api rquest is in invalud form 
    else {
        $data_status_arr = [json_encode([]),json_encode(array("status" => "400"))];
        return $data_status_arr;
    }
    // validates the team id is in the right form with a regex
    $team_id_regex_val = '/^[0-9]{1,5}$/';
    if (preg_match($team_id_regex_val,$resource[1]) == false) {
        $data_status_arr = [json_encode([]),json_encode(array("status" => "400"))];
        return $data_status_arr;
    }
    // sets up a prepared query to get information on all players that play for a specific team
    else {
        $query = "SELECT * from $table_name where $parameter = :value";
        $pre_query = $pdo -> prepare($query);
        $pre_query->bindParam(':value', $resource[1]);
        $pre_query->execute();
        $results = $pre_query->fetchAll();
        $data = json_encode($results);
        // If the api request is in a valid form but there are no players in a team
        // an empty string and a 204 error is sent
        if (!$results) {
            $data_status_arr = [$data,json_encode(array("status" => "204"))];
            return $data_status_arr;
        }
        else {
            // A function is called to created HATEOAS links for the http output body
            $data = hateoas_links($data,$table_name);
            // removes escape slashes from HATEOAS links
            $data = json_encode($data, JSON_UNESCAPED_SLASHES);
            // retunrs the json encoded data with a 200 status code
            $data_status_arr = [$data,json_encode(array("status" => "200"))];
            return $data_status_arr;
        } 
    }
}

/**
 * Gets the information of one player on a team
 * 
 * args: $pdo - a connection to the DBMS
 *       $resource - the resource the user entered into the URI
 * 
 * returns: $data_status_arr - an array containing the content of the api request and the 
 *          status code of it
 * 
 */
function get_single_player($pdo,$resource) {

    $table_name = "Players";
    $parameter1 = "playsFor";
    $parameter2 = "playerId";
    // creates a regex value to checks the teamId and playerId are of a valid form
    $id_regex_val = '/^[0-9]{1,5}$/';
    if (preg_match($id_regex_val,$resource[1]) == false || preg_match($id_regex_val,$resource[3]) == false) {
        $data_status_arr = [json_encode([]),json_encode(array("status" => "400"))];
        return $data_status_arr;
    }
    // sets up a prepared sql query to find the information of one player on a specific team
    else {
        $query = "SELECT * from $table_name where $parameter1 = :team_id AND $parameter2 = :player_id";
        $pre_query = $pdo -> prepare($query);
        $pre_query->bindParam(':team_id', $resource[1]);
        $pre_query->bindParam(':player_id', $resource[3]);
        $pre_query->execute();
        $results = $pre_query->fetchAll();
        $data = json_encode($results);
        // If the api request is in a valid form but there are no players in a team
        // an empty string and a 204 error is sent
        if (!$results) {
            $data_status_arr = [$data,json_encode(array("status" => "204"))];
            return $data_status_arr;
        }
        else {
            // A function is called to created HATEOAS links for the http output body
            $data = hateoas_links($data,$table_name);
            // removes escape slashes from HATEOAS links
            $data = json_encode($data, JSON_UNESCAPED_SLASHES);
            // retunrs the json encoded data with a 200 status code
            $data_status_arr = [$data,json_encode(array("status" => "200"))];
            return $data_status_arr;
        } 
    }
}

/**
 * Creates new player entires in the database for a specific team
 * 
 * args: $pdo - a connection to the DBMS
 *       $resource - the resource the user entered into the URI
 *       $input - the JSON data the client inputted
 * 
 * returns: $data_status_arr - an array containing the content of the api request and the 
 *          status code of it
 */
function create_data($pdo,$resource,$input) {
    // checks with regex the teamId is of a valid form
    $id_regex_val = '/^[0-9]{1,5}$/';
    // checks the api request is of a valid form
    if (count($resource) != 2 || $resource[0] != "teams"){
        $data_status_arr = [json_encode([]),json_encode(array("status" => "400"))];
        return $data_status_arr;
    }
    else if (preg_match($id_regex_val,$resource[1]) == false || preg_match($id_regex_val,$resource[1]) == false) {
        $data_status_arr = [json_encode([]),json_encode(array("status" => "400"))];
        return $data_status_arr;       
    }
    // calls json_validate function which checks the data doesnt contain any nulls
    // and that the data conforms to the DBMS
    else if (json_validate($input) == false) {
        $data_status_arr = [json_encode([]),json_encode(array("status" => "400"))];
        return $data_status_arr;
    }
    // sets up a prepared sql query to add a player to the database
    else{
        try{
            $table_name = "Players";
            $sql_inset_values = $input;
            // calls a function which queries the DBMS to get find the largest playerId
            // The newly inserted player then gets the id, (largest id) + 1
            $player_id = get_new_playerID($pdo);
            $query = "INSERT into $table_name (playerId, playerSurname, playerForname, playerNation, playerBirth, playsFor) 
                    values (:playerId, :playerSurname, :playerForname, :playerNation, :playerBirth, :playsFor)";
            $pre_query = $pdo -> prepare($query);
            $pre_query ->bindParam(':playerId',$player_id);
            $pre_query ->bindParam(':playerSurname',$sql_inset_values['playerSurname']);
            $pre_query ->bindParam(':playerForname',$sql_inset_values['playerForname']);
            $pre_query ->bindParam(':playerNation',$sql_inset_values['playerNation']);
            $pre_query ->bindParam(':playerBirth',$sql_inset_values['playerBirth']);
            $pre_query ->bindParam(':playsFor',$resource[1]);
            
            $pre_query->execute();
            
            // creates a second prepared statement to get the newly created data
            // this is what will be shown in the http body
            $query_2 = "SELECT * from $table_name where playerId = :player_id";
            $pre_query_2 = $pdo -> prepare($query_2);
            $pre_query_2 -> bindParam(':player_id',$player_id);
            $pre_query_2 -> execute();
            $results = $pre_query_2 -> fetchAll();
            $data = json_encode($results);

            // calls find_playsFor which starts the process of updating the avg age of the team
            // for which a new player was just added to 
            find_playsFor($pdo,$player_id);
            // A function is called to created HATEOAS links for the http output body
            $data = hateoas_links($data,$table_name);
            // removes escape slashes from HATEOAS links
            $data = json_encode($data, JSON_UNESCAPED_SLASHES);
            // retunrs the json encoded data with a 200 status code
            $data_status_arr = [$data,json_encode(array("status" => "200"))];
            return $data_status_arr;

        } catch (PDOException $e) {
            $data_status_arr = [json_encode([]),json_encode(array("status" => "400"))];
            return $data_status_arr;     
        }  
        


    }
}

/**
 * Queries the DBMS to find the player with the largest Id number
 * 
 * args: $pdo - provides a connection to the DBMS
 * 
 * returns: $new_id - the id for the newly added player which is 1 + (previous highest id number)
 */
function get_new_playerID($pdo) {
    // sets up a prepared sql query to find the largest player id value
    $query = "SELECT MAX(playerId) FROM Players";
    $pre_query = $pdo -> prepare($query);
    $pre_query->execute();
    $results = $pre_query->fetch();
    $max_id = $results["MAX(playerId)"];
    // incriments the largest player id value by one to create a new id
    $new_id = $max_id + 1;
    return $new_id;
}

/**
 * Valides the client entered JSON data
 * 
 * args: $input - the client inputted JSON data
 * 
 * Returns: False - when the JSON data fails validation
 *          True - when the JSON data passes validation
 */
function json_validate($input){
    // creates a list of the attritubutes names and dictionary of thiet assoiciated regex values
    $attribute_names = ["playerSurname","playerForname","playerNation","playerBirth"];
    $regex_list = ["playerSurname" => "/[a-zA-Z]{1,30}$/","playerForname" => "/[a-zA-Z]{1,30}$/","playerNation" => "/[a-zA-Z]{1,30}$/","playerBirth" => "/^(19[1-9]{2}|20[0-2]{1}[0-9]{1})-(0[1-9]{1}|1[0-2]{1})-(0[1-9]{1}|1[0-9]{1}|2[0-9]{1}|3[0-1]{1})$/"];
    // checks over the JSON data
    // checks that the values in the data are not null
    if ($input == null) {
        return false;
    }
    foreach ($input as $key => $value){
        if ($value == null) {
            return false;
        }
        // checks that the attributes names are correct
        else if (in_array($key,$attribute_names) == false) {
            return false;
        }
        // checks the values all conform to the DBMS standards
        $regex = $regex_list[$key];
        if (preg_match($regex,$value) == false) {
            return false;
        }
    }
    // If JSON data is valid returns True
    return True;
}

/**
 * This function deletes a player from the database via api request
 * 
 * args: $pdo - a connection to the DBMS
 *       $resource - the resource the user entered into the URI
 *       $input - the JSON data the client inputted
 * 
 * returns: $data_status_arr - an array containing the content of the api request and the 
 *          status code of it
 */
function delete_data($pdo,$resource) {
    // checks the api is in a valid form
    // checks the player id and team id are valid with a regex 
    $player_id_regex_val = '/^[0-9]{1,5}$/';
    if (count($resource) != 4 || $resource[0] != "teams" || $resource[2] != "players"){
        $data_status_arr = [json_encode([]),json_encode(array("status" => "400"))];
        return $data_status_arr;
    }
    else if (preg_match($player_id_regex_val,$resource[1]) == false || preg_match($player_id_regex_val,$resource[3]) == false) {
        $data_status_arr = [json_encode([]),json_encode(array("status" => "400"))];
        return $data_status_arr;
    }
    // creates a prepared sql query to delete a player from the database
    else {
        $table_name = "Players";
        $parameter1 = "playsFor";
        $parameter2 = "playerId";
        $query = "DELETE FROM $table_name WHERE $parameter1 = :team_id AND $parameter2 = :player_id ";
        $pre_query = $pdo -> prepare($query);
        $pre_query -> bindParam(':team_id',$resource[1]);
        $pre_query -> bindParam(':player_id',$resource[3]);
        $pre_query -> execute();
        // reference this 
        $rows_affected = $pre_query -> rowCount();
        // checks if any rows were affected
        // if non-were meaning there is no player matching api player id to delete
        if ($rows_affected == 0) {
            // a 204 status code is sent
            $data_status_arr = [json_encode([]),json_encode(array("status" => "204"))];
            return $data_status_arr;
        }
        else {
            // if a player is deleted the function call_avg_age this starts the process of updating the avg
            // age of a team that the player was just deleted from
            cal_avg_age($pdo,$resource[1]);
            $data_status_arr = [json_encode([]),json_encode(array("status" => "200"))];
            return $data_status_arr;
        } 

    }
    
}

/**
 * Updates player information on a specific team via an api request
 * 
 * args: $pdo - a connection to the DBMS
 *       $resource - the resource the user entered into the URI
 *       $input - the JSON data the client inputted
 * 
 * returns: $data_status_arr - an array containing the content of the api request and the 
 *          status code of it
 */
function patch_data($pdo, $resource, $input) {
    # checks the api request is in a valid form
    # uses a regex, to check that teamId and playerId are in a valid form
    $id_regex_val = '/^[0-9]{1,5}$/';
    if (count($resource) != 4 || $resource[0] != "teams" || $resource[2] != "players"){
        $data_status_arr = [json_encode([]),json_encode(array("status" => "400"))];
        return $data_status_arr;
    }
    else if (preg_match($id_regex_val,$resource[1]) == false || preg_match($id_regex_val,$resource[3]) == false) {
        $data_status_arr = [json_encode([]),json_encode(array("status" => "400"))];
        return $data_status_arr;
    }
    else if (json_validate($input) == false) {
        $data_status_arr = [json_encode([]),json_encode(array("status" => "400"))];
        return $data_status_arr;     
    }
    // sets up a prepared sql query to update a players information
    else {
        $sql_update_attributes = "";
        $sql_update_values = [];
        // finds all the attriubtes that are specififed to be updates
        // creates a string in the form "attributes1=?"
        // this will be used in the prepared query
        foreach ($input as $key => $value) {
            $attribute = $key . "=?,";
            $sql_update_attributes .= $attribute;
            // adds the value that the attribute is to be updated to , to a list
            array_push($sql_update_values,$value);
        }
        // removes the last comma from the string
        /*
         * The function rtrim() I learnt from this source
         * PHP Documnetation "rtrim"
         * URL: https://www.php.net/manual/en/function.rtrim.php 
         * It is used to remove the last comma from a string
         */
        $sql_update_attributes = rtrim($sql_update_attributes, ",");
        // appends the teamId and playerId to the update list as the last two parameters
        // in the perpared sql statement are the teamId and playerId
        array_push($sql_update_values,$resource[1]);
        array_push($sql_update_values,$resource[3]);
        // creates a prepared sql statment to update the information of a specific player
        try {
            $table_name = "Players";
            $parameter1 = "playsFor";
            $parameter2 = "playerId";
            $query = "UPDATE $table_name SET $sql_update_attributes WHERE $parameter1 =? AND $parameter2 =?";
            $pre_query = $pdo -> prepare($query);
            $pre_query -> execute($sql_update_values);

            $rows_affected = $pre_query -> rowCount();
            // checks how many rows were affected
            // if none were affected there was no player of that Id to update
            if ($rows_affected == 0) {
                // a 204 status code is sent
                $data_status_arr = [json_encode([]),json_encode(array("status" => "204"))];
                return $data_status_arr;
            }
            // if a player was affected a get request is made to retrieve the new infomration
            // this is what will be show in the http response body
            else {
                $query = "SELECT * from $table_name where playerId = :player_id";
                $pre_query = $pdo -> prepare($query);
                $pre_query -> bindParam(':player_id',$resource[3]);
                $pre_query -> execute();
                $results = $pre_query -> fetchAll();
                $data = json_encode($results);
                // creates HATEOAS links for the api request
                $data = hateoas_links($data,$table_name);
                $data = json_encode($data, JSON_UNESCAPED_SLASHES);
                // starts the process of updating the avg age of the team where the player info was updated
                // this is so if their age was updates the team avg age will also be updated
                find_playsFor($pdo,$resource[3]);
                $data_status_arr = [$data,json_encode(array("status" => "200"))];
                return $data_status_arr;
            } 
        } catch (PDOException $e) {
            $data_status_arr = [json_encode([]),json_encode(array("status" => "400"))];
            return $data_status_arr;     
        }  
        
    }
}

/**
 * Find the team a specific player plays for
 * 
 * args: $pdo - provides a connection to the DBMS
 *       $player_id - the id of the player just created/updated 
 * 
 * returns: None
 */
function find_playsFor($pdo,$player_id) {
    // sets up a prepared query to find the team a specific player plays for
    $table_name = "Players";
    $query = "SELECT playsFor FROM $table_name WHERE playerId = :playerId";
    $pre_query = $pdo -> prepare($query);
    $pre_query -> bindParam(':playerId',$player_id);
    $pre_query -> execute();

    $team_id = $pre_query -> fetchAll();

    foreach ($team_id as $team_id) {
        $team_id = $team_id['playsFor'];
    }
    // calls function to calculate the average age of the team that was just found
    cal_avg_age($pdo,$team_id);

}

/**
 * Calculates the average age of a team
 * 
 * args: $pdo - provides a connection to the DBMS
 *       $team_id - the id of a team
 */
function cal_avg_age($pdo,$team_id) {
    // creates a prepared sql statement to find all of the birthdays of every player of a specific team
    $table_name = "Players";
    $query = "SELECT playerBirth FROM $table_name WHERE playsFor = :team_id";
    $pre_query = $pdo -> prepare($query);
    $pre_query -> bindParam(':team_id',$team_id);

    $pre_query -> execute();

    $results = $pre_query-> fetchAll();
    // finds the current date
    $current_date = new DateTime();
    $age_list = [];

    // iterates through all the birthdays of each player
    // calculates their age, then appends it to a list
    foreach ($results as $dob) {
        $dob = new DateTime($dob['playerBirth']);
        $age = $dob -> diff($current_date) ->y;
        $age_list.array_push($age_list,$age);
    }

    // sums the total age of all player
    $total_age = array_sum($age_list);
    // find how many players are in the team
    $num_players = count($age_list);
    // calcuates the new average age of that team
    $average_age = $total_age/$num_players;
    // calls a function to send this update to the database
    update_avg_age($pdo,$average_age,$team_id);
}

/**
 * Queries the DBMS to update the average age of a team
 * 
 * args: $pdo - a connection to the BDMS
 *       $average_age - the average age of the team
 *       $team_id - the id of the team   
 * 
 * returns: None
 */
function update_avg_age($pdo,$average_age,$team_id) {
    // sets up a prepared sql query to update the team which had a player
    // created/deleted/updated to change their average age to the new value
    $query = "UPDATE Teams SET avgAge = :average_age WHERE teamId = :team_id ";
    $pre_query = $pdo -> prepare($query);
    $pre_query -> bindParam(':average_age',$average_age);
    $pre_query -> bindParam(':team_id',$team_id);
    $pre_query -> execute();    
}

/**
 * This function creates HATEOAS links for GET,POST and PATCH api requests
 * 
 * args: $data - the output of the initial api request
 *       $table_name - the table which was queried in the api request
 * 
 * returns: $data_hateoas - the result of the initial api request, with hateoas links attached
 */
function hateoas_links($data,$table_name) {
    // decodes the json data
    $data = json_decode($data,true);
    $data_hateoas = [];
    // checks to see which table was queried for the initial api request
    if ($table_name == "Teams") {
        // iterates through all the output of the api request
        // for each of them construsts the necessary HATEOAS links
        foreach ($data as $item) {
            $team_id = $item['teamId'];
            $array = array (
                "href" => "/teams/".$team_id,
                "method" => "GET",
                "rel" => "self"
            );
            // combines the data and their associated HATEOAS links
            $combined_array = array('data' => $item,'links' => $array);
            $data_hateoas = array($data_hateoas,$combined_array);
        }
        // returns this new array as the final output data
        return $data_hateoas;
    }
    else if ($table_name == "Players") {
        // iterates through all the output of the api request
        // for each of them construsts the necessary HATEOAS links
        foreach ($data as $item) {
            $team_id = $item["playsFor"];
            $player_id = $item["playerId"];
            $array = array ( 
                ["href" => "/teams/".$team_id."/players/".$player_id,
                "method" => "GET",
                "rel" => "self",], 
                ["href" => "/teams/".$team_id."/players/".$player_id,
                "method" => "DELETE",
                "rel" => "delete"],
                ["href" => "/teams/".$team_id."/players/".$player_id,
                "method" => "PATCH",
                "rel" => "update"]
            );
            // combines the data and their associated HATEOAS links
            $combined_array = array('data' => $item,'links' => $array);
            $data_hateoas = array($data_hateoas,$combined_array);
        }
        // returns this new array as the final output data
        return $data_hateoas;
    }

}
?>