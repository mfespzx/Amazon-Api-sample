class amazonApi 
{

		function main($keyword) {
			// define
			define('ACCESSKEY_ID', 'ACCESSKEY_ID');
			define('SECRET_ACCESSKEY', 'SECRET_ACCESSKEY');
			define('ASSOCIATE_ID', 'ASSOCIATE_ID');
			define('RESPONSE_GROUP','Medium,Images,ItemAttributes,OfferSummary');
			define('PERPAGE','10');
			define('DELTA','5');
			define('ITEM_MIN','1');
			define('ITEM_MAX','100');
			define('PROGRAM_URL','');
			define('IMAGEDIR','');
			define('NOIMAGE','NOIMGDIR/NOIMGFILE');
			define('TITLE_MAX','60');

			// initialize variables
			$index = array('Beauty', 'Apparel', 'Music', 'Photo', 'VideoGames', 'Books', 'DVD', 'Electronics', 'Grocery', 'HealthPersonalCare', 'Kitchen', 'MP3Downloads', 'MusicTracks', 'Shoes', 'Software', 'SportingGoods', 'Toys', 'Watches');
			$keys = array_keys($index);
			shuffle($index);
			foreach($index as $key){
				$index[] = $key;
			}
			array_unshift($index, 'Automotive');
			$index[] = 'Hobbies';
			$keyword = urlencode($keyword);
			$sortproperty = ($_GET['sort']);
			if ($sortproperty) {
				$sort = $sortproperty;
			} else {
				$sort = 'salesrank';exit;
			}
			$page_id='1';
			 
			$page_id = ($_GET['pageID']);
			for ($i = 0; $i < count($index); $i++) {
				$item_array = $this->change_index($index[$i], $keyword, $sort, $page_id);
				if ($this->show_item($item_array)) {
					echo $index[$i] . "<br />\n";
					$result = $this->show_item($item_array);
					break;
				} else {
					continue;
				}
			}
			if ($this->show_item($item_array)) {
				return $result;
			}
		}

		function change_index($index, $keyword, $sort, $page_id) {
			$ret_item_by_keyword = $this->get_item_by_keyword($index, $keyword);
			$item_array = $this->get_item_array($ret_item_by_keyword, $keyword);
			if ($item_array) {
				return $item_array;
			}
		}

		function show_item($item_array) {
			if ($item_array == '') {
				return;
			}
			$count = 0;
			$bufItem = "<ul class=\"amazonItem\"><span class=\"amaTop\">PR(amazon.com)</span>\n";
			foreach ($item_array as $item) {
				if ((($count + 1) % 2 ) == 0) {
					$bufItem .= '<li class="r">';
				} else {
					$bufItem .= '<li class="l">';
				}
				list($width, $height, $type, $attr) = getimagesize($item[image]);
				$pad = ( 160 - ( $height * 0.6 ) ) / 2;
				$pad2 = ( 100 - ( $width * 0.6) ) / 2;
				$bufItem .= '<div class="imgfrm" style="padding-top:' .$pad. 'px;">';
				$bufItem .= '<a href="#' . $item[asin]. '" class="nyroModal" title="' .$item[title] . '"><img class="thumb" src="' .$item[image]. '" border="0" style="width:' .($width * 0.6). 'px; height:' . ($height * 0.6) . 'px; padding-left:' . $pad2 . 'px;" alt="' . $item[title] . '"></a>';
				$bufItem .= '<div id="' . $item[asin] . '" style="display: none;"><a href="' .$item[url]. '" class="fade" title="Product Description(amazon.com)" target="_blank" rel="nofollow"><img src="' . $item[imageL] . '" /><br />';
				$bufItem .= '<span style="display: block;margin-top: 10px;">' .$item[title]. '</span></a>';
				$bufItem .= '</div>';
				$bufItem .= '</div>';
				$bufItem .= '<br />';
				$bufItem .= '<div class="txtfrm">';
				$bufItem .= '<span class="num">';
				$pagenum = ($_GET['pageID']);
				if( !$pagenum ){$pagenum = 1;}
				$bufItem .= ( $pagenum * 10 - 10 ) +( $count + 1 );
				$bufItem .= '</span>';
				$bufItem .= '<strong>';
				$bufItem .= '<a href="' . $item[url] . '" class="title fade" title="Product Description(amazon.com)" target="_blank" rel="nofollow">' . $item[title] . '</a>';
				$bufItem .= '</strong><br />';
				$bufItem .= '';
				$bufItem .= '<div class="feat">';
				$bufItem .= '<span style="display: block; text-align: left; height: 3.4em; overflow: hidden;">';
				$bufItem .= '' . $item[feature] . '</span>';
				$bufItem .= '<strong class="price">';
				$bufItem .= '' . $item[price_name] . '：' . $item[price] . '</strong>';
				$bufItem .= '<br />';
				$bufItem .= '';
				$bufItem .= '</div>';
				$bufItem .= '</div></li>';
				if ((($count + 1) % 2) == 0) {
					$bufItem .= '<br clear="all" />';
				}
				if ($count == 0) {
					$bufItem2 = '<a href="' . $item[url] . '" title="Product Description(amazon.com)" target="_blank">' . $item[title] . '</a>';
				}
				$count++;
			}
			$bufItem .= "</ul>\n";
			$bufItem .= '<br clear="all" />'
			;
			return array($bufItem, $bufItem2);
		}

		// get item array
		function get_item_array($ret, $keyword) {
			$tab = $ret->Items->TotalResults;
			if (isset($ret->Items)) {
				$i = 0;
				foreach ($ret->Items->Item as $item) {
					if ($i == 4) {
						break;
					}

					// ASIN
					$item_array[$i][asin] = $item->ASIN;
					// image
					if (isset($item->MediumImage->URL)) {
						$item_array[$i][image] = $item->MediumImage->URL;
					}else{
						$item_array[$i][image] = IMAGEDIR.NOIMAGE;
					}
					// large image
					if(isset($item->LargeImage->URL)){
						$item_array[$i][imageL] = $item->LargeImage->URL;
					}else{
						$item_array[$i][imageL] = IMAGEDIR.NOIMAGE;
					}
					// title
					$item_array[$i][title]
					 = mb_strimwidth($item->ItemAttributes->Title, 0, TITLE_MAX, "...");
					// URL
					$item_array[$i][url] = $item->DetailPageURL;
					// feature
					for($j = 0; $j < count($item->ItemAttributes->Feature); $j++){
						if (mb_strlen($item->ItemAttributes->Feature[$j]) >= 1) {
							$item_array[$i][feature] .= $item->ItemAttributes->Feature[$j];
						}
					}
			        // price
					if (isset($item[ItemAttributes][ListPrice][FormattedPrice])
						&& $item[ItemAttributes][ListPrice][Amount]>10) {
						$item_array[$i][price_name]='PRICE';
						$item_array[$i][price]=$item[ItemAttributes][ListPrice][FormattedPrice];
					} else if(isset($item[OfferSummary][LowestNewPrice][FormattedPrice])) {
						$item_array[$i][price_name]='PRICE';
						$item_array[$i][price]=$item[OfferSummary][LowestNewPrice][FormattedPrice];
					} else if(isset($item[OfferSummary][LowestUsedPrice][FormattedPrice])){
						$item_array[$i][price_name]='USED PRICE';
						$item_array[$i][price]=$item[OfferSummary][LowestUsedPrice][FormattedPrice];
					} else {  // no price
						$item_array[$i][price_name]='price not determined';
						$item_array[$i][price]='---';
					}
					$i++;
		        }
		        $itemCheck = NULL;
		        for ($i = 0; $i < 4; $i++) {
			        $itemCheck .= $item_array[$i][title] . $item_array[$i][feature];
			    }
			    $checkWord = preg_replace("/(.*?) (.*?)/", "$2", $keyword);

		    }
		    return $item_array;
		}

		function get_item_by_keyword($index, $keyword) {
			$base_url = "http://ecs.amazonaws.com/onca/xml";
			$params = array(
				'AWSAccessKeyId' => ACCESSKEY_ID,
				'AssociateTag' => ASSOCIATE_ID,
				'Version' => "2010-11-01",
				'Operation' => "ItemSearch",
				'Service' => "AWSECommerceService",
				'ResponseGroup' => "ItemAttributes,Images",
				'Availability' => "Available",
				'Condition' => "All",
				'Operation' => "ItemSearch",
				'SearchIndex' => $index , //Change search index if required, you can also accept it as a parameter for the current method like $searchTerm
				'Keywords' => $keyword);

			if(empty($params['AssociateTag'])) {
				unset($params['AssociateTag']);
			}

			// Add the Timestamp
			$params['Timestamp'] = gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z", time());

			// Sort the URL parameters
			$url_parts = array();
			foreach(array_keys($params) as $key)
			$url_parts[] = $key . "=" . str_replace('%7E', '~', rawurlencode($params[$key]));
			sort($url_parts);

			// Construct the string to sign
			$url_string = implode("&", $url_parts);
			$string_to_sign = "GET\necs.amazonaws.com\n/onca/xml\n" . $url_string;

			// Sign the request
			$signature = hash_hmac("sha256", $string_to_sign, SECRET_ACCESSKEY, TRUE);

			// Base64 encode the signature and make it URL safe
			$signature = urlencode(base64_encode($signature));

			$url = $base_url . '?' . $url_string . "&Signature=" . $signature;
			$ret = simplexml_load_file($url);
			return $ret;
		}

} // END amazonApi
