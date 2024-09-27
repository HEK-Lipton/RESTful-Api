// This prevents the page from being refreshed every time an api request is made
/*
 * The code from lines 5-7 has been taken from  
 * SAGAR NANERA "How do you prevent a page reload in javascript"
 * Quora september 27th 2023
 * URL: https://www.quora.com/How-do-you-prevent-a-page-reload-in-JavaScript#:~:text=%C2%B7%2010mo-,To%20prevent%20a%20page%20reload%20in%20JavaScript%2C%20you%20can%20use,normally%20trigger%20a%20page%20reload.
 */ 
document.getElementById("button").addEventListener("click", function(){
event.preventDefault();
});
console.log("script loaded")

/**
 * This function clears the html fields that relate
 * to the http response and status code
 * 
 * args: None
 * 
 * returns: None
 */
function clear_output() {
    // finds the html elements that relate to displaying the http body response and the status code
    var status_par = document.getElementById("http_status_code")
    var body_par = document.getElementById("http_response")
    // sets both of their inner text to empty strings
    status_par.innerText = ""
    body_par.innerText = ""
}

/**
 * This function sends http requests
 * 
 * args: None
 * 
 * returns: None
 */
function sendRequest() {
    console.log("Sending Request")
    // finds the html form which contains the request method, resource and JSON data entry field
    var form = document.getElementById("http_form")
    // gets the form data
    var data = new FormData(form)
    // creates a new http requests
    var request = new XMLHttpRequest()
    // this part of the url always stays the same
    var url = "https://student.csc.liv.ac.uk/~sghlipto/v1";
    // concats the url with the user inputted resource
    url += data.get('resource');
    request.open(data.get('method'),url,true)
    // gets the json data
    var request_body = data.get('json_data')

    // This function runs when the request has loaded
    request.onload = function() {
        // finds the two html elements that display the http reponse body and status code
        var status_par = document.getElementById("http_status_code")
        var body_par = document.getElementById("http_response")
        // Sets the inner text of the html status code element to be the status code sent back 
        // by the http request
        status_par.innerText = request.status
        console.log(request.status)
        // if a status code is sent that doesnt come with data in the http response body 
        // a custom error/info message is sent to the body
        if (request.status == "204") {
            body_par.innerText = "Info: API request was valid but there was no content matching it"
        }
        else if (request.status == "400") {
            body_par.innerText = '{ "Error" : "Invalid API request. Please check your resource and JSON data is in the right format"}'
        }
        // if a successful http request has been made then echoed response from rest.php is dispayed in the response body
        else{
            body_par.innerText = request.responseText
        }
        
    }

    request.send(request_body)
}


