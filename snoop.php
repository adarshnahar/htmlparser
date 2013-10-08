<?php


			// foreach($html3->find('h1[class=blue_bg]') as $element){
			// 	$job_title_array_string = str_replace("  ", " ", trim($element->plaintext));
			// 	$job_title_array = explode(" job in ", $job_title_array_string);
			// 	//$job_title_array1[] = $job_title_array[0];
			// 	$data1['job_title'] = $job_title_array[0];
				
			// }


// //error_reporting(1);
// //ini_set('display_errors', 1);

// including the dom library
include('simple_html_dom.php');

// initializing the object
$html = new simple_html_dom();
$html2 = new simple_html_dom();

// parsing html
$html2 = file_get_html('http://www.jobfinder.ng');

// Find all links
$i=0;
foreach($html2->find('div[class=main-tab]') as $element){

	foreach ($element->find('tr') as $element1) {
		
		foreach ($element1->find('a') as $element2) {

			// searching and storing location url
			if(preg_match("/^\/vacancies\/9/", $element2->href))
	    	{
	    		$location_url_array[$i] = $element2->href;
	    		//$i++;
			}
		}	
	}
}

foreach ($location_url_array as $key => $value) {
	
	// genrating the page url
	$url = "http://www.jobfinder.ng".$value;

	// parsing html
	$html = file_get_html($url);

	//find no of results
	foreach($html->find('td[class=td-result]') as $result){

		$search_result = $result->innertext;
		$search_result_array = explode(":", trim($search_result));
		$no_of_search_results_array = explode("of", trim($search_result_array[1]));
		$no_of_search_results = trim($no_of_search_results_array[1]);
		$no_of_search_results = (float)$no_of_search_results;
		if($no_of_search_results > 10)
		{
			// getting no of pages
			$no_of_pages = $no_of_search_results/10;
			$mode = $no_of_search_results%10;
			if($mode != 0)
			{
				$no_of_pages = ((int)$no_of_pages)+1;
			}

			$total_no_of_pages = $no_of_pages-1;
		}
	}

	for($i=0;$i<=$total_no_of_pages;$i++)
	{
		$url = $url."/".$i;

		// parsing html
		$html = file_get_html($url);

		//Find all links
		$i=0;
		foreach($html->find('a') as $element){

			// searching and storing jobs
			if(preg_match("/^\/career/", $element->href))
		    {
		       	$carrers_links[$i] = $element->href;
		       	$i++;
		    }
		}

		$j=0;

		foreach ($carrers_links as $key => $value) {
			
			$url = "http://www.jobfinder.ng".$value;

			// parsing html
			$html1 = file_get_html($url);
			
			foreach($html1->find('div[class=search-job-result]') as $element){
				foreach ($element->find('tr') as $element1) {
					$element_array[$j] = $element1->innertext;
					$j++;
				}
			}

			$k=0;
			foreach ($element_array as $key => $value) {

				if(strpos($value, ":&nbsp;"))
				{
					if(strpos($value, "Address"))
					{
						$element_array1[$k] = str_replace(" ", "", preg_replace("/(\\(Show on map\\))/is","", trim(preg_replace("/(.<script .*>).*(<\/script>.)/", "", strip_tags($value,"<script>")))));
					}
					else
					{
						 $string = explode(":", trim(strip_tags($value)));
						$string1[0] = trim($string[0]);
						$string1[1] = trim(str_replace("&nbsp;", "", $string[1]));

						 $final_string = implode(":", $string1);
						 $element_array1[$k] = $final_string;
						//$element_array1[$k] = str_replace('   ', ' ', trim(strip_tags($value)));
					}
				}
				else
				{
					$element_array1[$k] = trim(strip_tags($value, "<p>"));
				}
				
				$k++;
			}

			$count = count($element_array1);
			
			//intializing search fields
			$search_keys = array("Recruiter","Industry","Job Type","State","City","Address","Salary");
			
			// storing data for database
			foreach ($element_array1 as $job_element) {

				if(strpos($job_element, ":"))
				{
					 $job_element1 = explode(":", $job_element);

					foreach ($search_keys as $search_key) {
						
						if(in_array($search_key, $job_element1))
						{
							$data[$job_element1[0]] = $job_element1[1];
						}
					}
				}
					
			}

			$data['Description'] = $element_array1[$count-1];
			
		    echo "<pre>";
			print_r($data);
			break;	
		} 
	}
}


?>