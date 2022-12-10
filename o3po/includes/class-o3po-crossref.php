<?php

/**
 * Encapsulates the interface with the external service Crossref.
 *
 * @link       https://quantum-journal.org/o3po/
 * @since      0.3.0
 *
 * @package    O3PO
 * @subpackage O3PO/includes
 */

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-bibentry.php';

/**
 * Encapsulates the interface with the external service Crossref.
 *
 * Provides methods to interface with Crossref.
 *
 * @package    O3PO
 * @subpackage O3PO/includes
 * @author     Christian Gogolin <o3po@quantum-journal.org>
 */
class O3PO_Crossref {

        /**
         * Http post meta data to Crossref.
         *
         * Submits meta-data in Crossref's xml format via http post to their servers.
         *
         * From the command line one could do roughly the same with curl as follows:
         *
         * curl -F 'operation=doMDUpload' -F 'login_id=XXXX' -F 'login_passwd=XXXX' -F 'fname=@/home/cgogolin/tmp/crossref-test.xml' https://test.crossref.org/servlet/deposit -v

         * @since    0.3.0
         * @access   private
         * @param    string   $doi_batch_id    Batch id of this upload.
         * @param    string   $crossref_xml    The xml to upload.
         * @param    string   $crossref_id     The id for which to submit this upload.
         * @param    string   $crossref_pw     The password corresponding to the crossref_id.
         * @param    string   $crossref_url    The url of the crossref server to upload to.
         */
    public static function remote_post_meta_data_to_crossref( $doi_batch_id, $crossref_xml, $crossref_id, $crossref_pw, $crossref_url ) {

            // Construct the HTTP POST call
		$boundary = wp_generate_password(24);
		$headers = array( 'content-type' => 'multipart/form-data; boundary=' . $boundary );
		$post_fields = array(
			'operation' => 'doMDUpload',
 			'login_id' => $crossref_id,
 			'login_passwd' => $crossref_pw,
                             );
        $payload = '';
            // Add the standard POST fields
		foreach ( $post_fields as $name => $value ) {
            $payload .= '--' . $boundary;
            $payload .= "\r\n";
            $payload .= 'Content-Disposition: form-data; name="' . $name . '"' . "\r\n\r\n";
            $payload .= $value;
            $payload .= "\r\n";
		}
            // Attach the xml data
		if ( !empty($crossref_xml) && !empty($doi_batch_id) ) {
            $payload .= '--' . $boundary;
            $payload .= "\r\n";
            $payload .= 'Content-Disposition: form-data; name="' . 'fname' . '"; filename="' . $doi_batch_id . '.xml' . '"' . "\r\n";
            $payload .= 'Content-Type: text/xml' . "\r\n";
            $payload .= "\r\n";
            $payload .= $crossref_xml;
            $payload .= "\r\n";
		}
		$payload .= '--' . $boundary . '--';

		$response = wp_remote_post( $crossref_url, array(
                                        'headers' => $headers,
                                        'body' => $payload,
                                                         ) );

        return $response;
    }

        /**
         * Retrieve cited-by information for a given doi from Crossref.
         *
         * Uses Crossref's cited-by service.
         *
         * @since    0.3.0
         * @access   private
         * @param    string   $crossref_url    The url of the crossref server from which to fetch cited by information.
         * @param    string   $crossref_id     The id for which to submit this upload.
         * @param    string   $crossref_pw     The password corresponding to the crossref_id.
         * @param    string   $doi             The doi for which cited-by data is to be retrieved.
         * @param    int      $storage_time    The maximal time for which to cache the response in a transient (default 600 seconds).
         * @param    int      $timeout         Maximum time to wait for a response (default 6 seconds).
         * @return   mixed    Response of wp_remote_get() from Crossref or WP_Error.
         */
    private static function remote_get_cited_by( $crossref_url, $crossref_id, $crossref_pw, $doi, $storage_time=60*10, $timeout=10 ) {

        $request_url = $crossref_url . '?usr=' . urlencode($crossref_id).  '&pwd=' . urlencode($crossref_pw) . '&doi=' . urlencode($doi) . '&include_postedcontent=true';

        $response = get_transient('get_crossref_cited_by_' . $request_url);
        if(empty($response)) {
            $response = wp_remote_get($request_url, array('timeout' => $timeout));
            if(is_wp_error($response))
                return $response;
            set_transient('get_crossref_cited_by_' . $request_url, $response, $storage_time);
        }

        return $response;
    }

        /**
         * Retrieve cited-by information in xml format from Crossref.
         *
         * Uses Crossref's cited-by service to retrieve information about works
         * citing the given DOI in xml format.
         *
         * See http://data.crossref.org/reports/help/schema_doc/crossref_query_output2.0/query_output2.0.html
         * for more information.
         *
         * @since    0.3.0
         * @access   public
         * @param    string   $crossref_url    The url of the crossref server to upload to.
         * @param    string   $crossref_id     The id for which to submit this upload.
         * @param    string   $crossref_pw     The password corresponding to the crossref_id.
         * @param    string   $doi             The doi for which cited-by data is to be retrieved.
         */
    public static function get_cited_by_xml_body( $crossref_url, $crossref_id, $crossref_pw, $doi ) {

        try
        {
            $use_errors=libxml_use_internal_errors(true);

            $response = static::remote_get_cited_by($crossref_url, $crossref_id, $crossref_pw, $doi);

            if(is_wp_error($response))
                return $response;
            else if(empty($response['body']) )
                throw new Exception("Could not fetch cited-by data for " . $doi . " from Crossref. No response.");
            else
            {
                $xml = simplexml_load_string($response['body']);
                if ($xml === false) {
                    $error = "Could not fetch cited-by data for " . $doi . " from Crossref. This is normal if the DOI was registered recently.";
                    if(!empty(libxml_get_errors()))
                    {
                        /* foreach(libxml_get_errors() as $e) { */
                        /*     $error .= " " . trim($e->message) . "."; */
                        /* } */
                        libxml_clear_errors();
                    }
                    throw new Exception($error);
                }
                $body = $xml->query_result->body[0];
                return $body;
            }
        } catch(Throwable $e) {
            return new WP_Error("exception", $e->getMessage());
        } finally {
            libxml_use_internal_errors($use_errors);
        }
    }


        /**
         * Get cited by information for a given DOI as an array of bibtenries.
         *
         * @since    0.3.0
         * @access   public
         * @param    string   $crossref_url    The url of the crossref server to upload to.
         * @param    string   $crossref_id     The id for which to submit this upload.
         * @param    string   $crossref_pw     The password corresponding to the crossref_id.
         * @param    string   $doi             The doi for which cited-by data is to be retrieved.
         * @return   array Array of bibentries citing the given DOI
         *
         */
    public static function get_cited_by_bibentries( $crossref_url, $crossref_id, $crossref_pw, $doi ) {

        try
        {
            $body = O3PO_Crossref::get_cited_by_xml_body($crossref_url, $crossref_id, $crossref_pw, $doi);

            if(is_wp_error($body))
                return $body;

            $citation_number = 0;
            $bibentries = array();

            foreach ($body->forward_link as $f_link) {
                if(!empty($f_link->journal_cite))
                    $cite = $f_link->journal_cite;
                elseif(!empty($f_link->book_cite))
                    $cite = $f_link->book_cite;
                elseif(!empty($f_link->conf_cite))
                    $cite = $f_link->conf_cite;
                elseif(!empty($f_link->dissertation_cite))
                    $cite = $f_link->dissertation_cite;
                elseif(!empty($f_link->report_cite))
                    $cite = $f_link->report_cite;
                elseif(!empty($f_link->standard_cite))
                    $cite = $f_link->standard_cite;
                elseif(!empty($f_link->database_cite))
                    $cite = $f_link->database_cite;
                elseif(!empty($f_link->postedcontent_cite))
                    $cite = $f_link->postedcontent_cite;
                else
                    throw new Exception("Encountered the unhandled forward link type " . $f_link->children()[0]->getName() . " while looking for citations to DOI " . $doi . ".");

                $authors = array();
                if(!empty($cite->contributors->contributor))
                {
                    foreach ($cite->contributors->contributor as $contributor) {
                        $authors[] = new O3PO_Author($contributor->given_name, $contributor->surname);
                    }
                }

                $bibentries[] = new O3PO_Bibentry(
                    array(
                        'authors' => $authors,
                        'venue' => $cite->journal_title,
                        'title' => !empty($cite->title) ? $cite->title : $cite->article_title,
                        'collectiontitle' => !empty($cite->series_title) ? $cite->series_title : $cite->volume_title,
                        'volume' => $cite->volume,
                        'issue' => $cite->issue,
                        'page' => !empty($cite->first_page) ? $cite->first_page : $cite->item_number,
                        'year' => $cite->year,
                        'doi' => $cite->doi,
                        'isbn' => $cite->isbn,
                        'issn' => $cite->issn,
                        'type' => $cite->publication_type,
                        'institution' => $cite->institution_name,
                          ));
            }
        }
        catch(Throwable $e) {
            return new WP_Error("exception", $e->getMessage());
        }

        return $bibentries;
    }


        /**
         * Make XML for registering a DOI at Crossref.
         *
         * @since    0.4.1
         * @access   public
         * @param    string   $title           The title of the work.
         * @return   strong XML for registering a DOI at Crossref
         *
         */
    public static function generate_crossref_xml( $doi_batch_id, $timestamp, $title, $title_mathml, $abstract, $abstract_mathml, $number_authors, $author_given_names, $author_surnames, $author_name_styles, $author_orcids, $author_affiliations, $date_published, $pages, $doi, $affiliations, $journal, $volume, $parsed_bbl, $post_url, $pdf_pretty_permalink, $number_award_numbers, $award_numbers, $funder_identifiers, $funder_names, $crossref_crossmark_policy_page_doi, $email_address, $publisher, $eissn, $crossref_archive_locations, $journal_title, $journal_level_doi_suffix, $doi_prefix, $site_url, $orcid_url_prefix, $license_url ) {

        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<doi_batch version="4.4.2" xmlns="http://www.crossref.org/schema/4.4.2" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.crossref.org/schema/4.4.2 http://data.crossref.org/schemas/crossref4.4.2.xsd" xmlns:mml="http://www.w3.org/1998/Math/MathML" xmlns:jats="http://www.ncbi.nlm.nih.gov/JATS1" xmlns:ai="http://www.crossref.org/AccessIndicators.xsd" xmlns:fr="http://www.crossref.org/fundref.xsd">' . "\n";
        $xml .= '  <head>' . "\n";
            //a unique id for each batch
        $xml .= '    <doi_batch_id>' . esc_xml($doi_batch_id) . '</doi_batch_id>' . "\n";
            /* timestamp for batch integer representation of date and time that serves as a
             * version number for the record that is being deposited. Because CrossRef uses it as a
             * version number, the format need not follow any public standard and therefore the
             * publisher can determine the internal format. The schema format is a double of at
             * least 64 bits */
        $xml .= '    <timestamp>' . esc_xml($timestamp) . '</timestamp>' . "\n";
        $xml .= '    <depositor>' . "\n";
        $xml .= '      <depositor_name>' . esc_xml($publisher) . '</depositor_name>' . "\n";
        $xml .= '      <email_address>' . esc_xml($email_address) . '</email_address>' . "\n";
        $xml .= '    </depositor>' . "\n";
        $xml .= '    <registrant>' . esc_xml($publisher) . '</registrant>' . "\n";
        $xml .= '  </head>' . "\n";
        $xml .= '  <body>' . "\n";
        $xml .= '    <journal>' . "\n";
        $xml .= '      <journal_metadata language="en" reference_distribution_opts="any">' . "\n";
        $xml .= '	<full_title>' . esc_xml($journal) . '</full_title>' . "\n";
        $xml .= '	<abbrev_title>' . esc_xml($journal) . '</abbrev_title>' . "\n";
        if(!empty($eissn))
            $xml .= '	<issn media_type="electronic">' . $eissn . '</issn>' . "\n";
            // we don't have a coden
            // $xml .= '	<coden></coden>' . "\n";
            // Options for archive names are: CLOCKSS, LOCKSS Portico, KB, DWT, Internet Archive.
        if(!empty($crossref_archive_locations) && $journal === $journal_title)
        {
            $xml .= '	<archive_locations>' . "\n";
            foreach(preg_split('/\s*,\s*/u', $crossref_archive_locations)  as $archive_name)
                $xml .= '	  <archive name="' . esc_attr(trim($archive_name)) . '"></archive>' . "\n";
            $xml .= '	</archive_locations>' . "\n";
        }
        $xml .= '	<doi_data>' . "\n";
            // Add the journal level DOI of the journal
        if( !empty($doi_prefix) && !empty($journal_level_doi_suffix) )
            $xml .= '	  <doi>' . $doi_prefix .'/' . $journal_level_doi_suffix . '</doi>' . "\n";
            // timestamp for journal level doi data, not mandatory if already given in head
            // $xml .= '	  <timestamp></timestamp>' . "\n";
        $xml .= '	  <resource mime_type="text/html">' . esc_xml($site_url) . '</resource>' . "\n";
        $xml .= '	</doi_data>' . "\n";
        $xml .= '      </journal_metadata>' . "\n";
            // We don't have issues but volumes
        $xml .= '      <journal_issue>' . "\n";
        $xml .= '	     <publication_date media_type="online">' . "\n";
        $xml .= '	       <month>' . mb_substr($date_published, 5, 2) . '</month>' . "\n";
        $xml .= '	       <day>' . mb_substr($date_published, 8, 2) .'</day>' . "\n";
        $xml .= '	       <year>' . mb_substr($date_published, 0, 4) . '</year>' . "\n";
        $xml .= '	     </publication_date>' . "\n";
        $xml .= '	     <journal_volume>' . "\n";
        $xml .= '	       <volume>' . $volume . '</volume>' . "\n";
            //$xml .= '	         <publisher_item>{0,1}</publisher_item>' . "\n";
            //$xml .= '	         <archive_locations>{0,1}</archive_locations>' . "\n";
            //$xml .= '	         <doi_data>{0,1}</doi_data>' . "\n";
        $xml .= '	     </journal_volume>' . "\n";
        $xml .= '      </journal_issue>' . "\n";
        $xml .= '      <journal_article language="en" publication_type="full_text" reference_distribution_opts="any">' . "\n";
        $xml .= '	<titles>' . "\n";
            // Minimal face markup and MathML are supported in the title
        $xml .= '	  <title>' . (!empty($title_mathml) ? $title_mathml : esc_xml($title)) . '</title>' . "\n";
            // $xml .= '	  <subtitle>{0,1}</subtitle>' . "\n";
            // $xml .= '	  <original_language_title language="">{1,1}</original_language_title>' . "\n";
            // $xml .= '	  <subtitle>{0,1}</subtitle>' . "\n";
        $xml .= '	</titles>' . "\n";
        $xml .= '	<contributors>' . "\n";
            // we only have authors
            // $xml .= '	  <organization contributor_role="" language="" name-style="" sequence="">{1,1}</organization>' . "\n";
        for ($x = 0; $x < $number_authors; $x++) {
            $xml .= '	  <person_name contributor_role="author" sequence="' . ($x === 0 ? "first" : "additional") . '"';
            if ( !empty($author_name_styles[$x]) )
                $xml .= ' name-style="' . $author_name_styles[$x] . '"';
            $xml .= '>' . "\n";
            if ( !empty($author_given_names[$x]) )
                $xml .= '	    <given_name>' . esc_xml($author_given_names[$x]) . '</given_name>' . "\n";
            $xml .= '	    <surname>' . esc_xml($author_surnames[$x]) . '</surname>' . "\n";
                // $xml .= '	    <suffix>{0,1}</suffix>' . "\n";
            if ( !empty($author_affiliations) && !empty($author_affiliations[$x]) ) {
                foreach(preg_split('/\s*,\s*/u', $author_affiliations[$x], -1, PREG_SPLIT_NO_EMPTY) as $affiliation_num) {
                    if ( !empty($affiliations[$affiliation_num-1]) )
				     	$xml .= '	    <affiliation>' . esc_xml($affiliations[$affiliation_num-1]) . '</affiliation>' . "\n";
                }
            }
            if ( !empty($author_orcids) && !empty($author_orcids[$x]) )
                $xml .= '	    <ORCID authenticated="false">' . $orcid_url_prefix . $author_orcids[$x] . '</ORCID>' . "\n";
                // $xml .= '	    <alt-name>{0,1}</alt-name>' . "\n";
            $xml .= '	  </person_name>' . "\n";
        }
        $xml .= '	</contributors>' . "\n";
        if( !empty($abstract) || !empty($abstract_mathml) )
        {
            $xml .= '	<jats:abstract xml:lang="en">' . "\n";
            $xml .= '	  <jats:p>' . (!empty($abstract_mathml) ? $abstract_mathml : esc_xml($abstract)) . '</jats:p>' . "\n";
            $xml .= '	</jats:abstract>' . "\n";
        }
        $xml .= '	<publication_date media_type="online">' . "\n";
        $xml .= '	    <month>' . mb_substr($date_published, 5, 2) . '</month>' . "\n";
        $xml .= '	    <day>' . mb_substr($date_published, 8, 2) .'</day>' . "\n";
        $xml .= '	    <year>' . mb_substr($date_published, 0, 4) . '</year>' . "\n";
        $xml .= '	</publication_date>' . "\n";
            // we only have article numbers which should go into the publisher_item  below, but despite what Crossref says in their documentation they don't handle this propperly so we have to add it also here
        $xml .= '	<pages>' . "\n";
        $xml .= '	  <first_page>' . $pages . '</first_page>' . "\n";
            // $xml .= '	  <last_page>...</last_page>' . "\n";
        $xml .= '	</pages>' . "\n";
        $xml .= '	<publisher_item>' . "\n";
        $xml .= '	  <item_number item_number_type="article-number">' . $pages . '</item_number>' . "\n";
        $xml .= '	</publisher_item>' . "\n";
            // Now comes the Crossmark/Fundref funder information
        if(!empty($crossref_crossmark_policy_page_doi))
        {
            $xml .= '	<crossmark>' . "\n";
            $xml .= '	  <crossmark_version>1</crossmark_version>' . "\n";
            $xml .= '	  <crossmark_policy>' . esc_xml($crossref_crossmark_policy_page_doi) . '</crossmark_policy>' . "\n";
            $xml .= '	  <crossmark_domains>' . "\n";
            $xml .= '	    <crossmark_domain>' . "\n";
            $xml .= '	      <domain>' . substr($site_url, 8) . '</domain>' . "\n";
            $xml .= '	    </crossmark_domain>' . "\n";
            $xml .= '	  </crossmark_domains>' . "\n";
            $xml .= '	  <crossmark_domain_exclusive>false</crossmark_domain_exclusive>' . "\n";
            $xml .= '	  <custom_metadata>' . "\n";
            if($number_award_numbers > 0)
            {
                $xml .= '	    <fr:program name="fundref">' . "\n";
                for ($x = 0; $x < $number_award_numbers; $x++)
                {
                    if(!empty($award_numbers[$x]))
                    {
                        $xml .= '	      <fr:assertion name="fundgroup">' . "\n";
                        if(!empty($funder_names[$x]))
                        {
                            $xml .= '	        <fr:assertion name="funder_name">' . esc_xml($funder_names[$x]) . "\n";
                            if(!empty($funder_identifiers[$x]))
                                $xml .= '	          <fr:assertion name="funder_identifier">' . esc_xml($funder_identifiers[$x]) .'</fr:assertion>' . "\n";
                            $xml .= '	        </fr:assertion>' . "\n";
                        }
                        $xml .= '	        <fr:assertion name="award_number">' . esc_xml($award_numbers[$x]) .'</fr:assertion>' . "\n";
                        $xml .= '	      </fr:assertion>' . "\n";
                    }
                }
                $xml .= '	    </fr:program>' . "\n";
            }
                // access indications
            $xml .= '	    <ai:program name="AccessIndicators">' . "\n";
            $xml .= '	      <ai:free_to_read></ai:free_to_read>' . "\n";
            $xml .= '	      <ai:license_ref start_date="' . $date_published . '">' . esc_xml($license_url) . '</ai:license_ref>' . "\n";
            $xml .= '	    </ai:program>' . "\n";
            $xml .= '	  </custom_metadata>' . "\n";
            $xml .= '	</crossmark>' . "\n";
        }
            // for clinical trials, we don't have that
            // $xml .= '	<ct:program>{0,1}</ct:program>' . "\n";
            // for relations between programs
            // $xml .= '	<rel:program name="relations">{0,1}</rel:program>' . "\n";
            // we archive on the arXiv and not here
            // $xml .= '	<archive_locations><archive></archive></archive_locations>' . "\n";
        $xml .= '	<doi_data>' . "\n";
        $xml .= '	  <doi>' . esc_xml($doi) . '</doi>' . "\n";
            // not mandatory if already given in head
            // $xml .= '	  <timestamp>...</timestamp>' . "\n";
            // URL to landing page, content_version can be vor (version of record) or am (advance manuscript).
        $xml .= '	  <resource content_version="am" mime_type="text/html">' . esc_url($post_url) . '</resource>' . "\n";
            // think we don't need this
            // $xml .= '	  <collection multi-resolution="" property="">{0,unbounded}</collection>' . "\n";
            // add full text link for text-mining
        if(!empty($pdf_pretty_permalink))
        {
            $xml .= '<collection property="text-mining">' . "\n";
            $xml .= '<item>' . "\n";
            $xml .= '<resource>' . "\n";
            $xml .= esc_url($pdf_pretty_permalink) . "\n";
            $xml .= '</resource>' . "\n";
            $xml .= '</item>' . "\n";
            $xml .= '</collection>' . "\n";
        }
        $xml .= '	</doi_data>' . "\n";
            // the references
        if(!empty($parsed_bbl)) {
            $xml .= '	<citation_list>' . "\n";
            foreach($parsed_bbl as $n => $entry) {
                $xml .= '	  <citation key="' . $n . '">' . "\n";
                if( !empty($entry['doi']) )
                    $xml .= '	    <doi>' . esc_xml($entry['doi']) . '</doi>' . "\n";
                $xml .= '	    <unstructured_citation>' . esc_xml($entry['text']) . '</unstructured_citation>' . "\n";
                $xml .= '	  </citation>' . "\n";
            }
            $xml .= '	</citation_list>' . "\n";
        }
            // we don't usually have components, just single articles
            // $xml .= '	<component_list>{0,1}</component_list>' . "\n";
        $xml .= '      </journal_article>' . "\n";
        $xml .= '    </journal>' . "\n";
        $xml .= '  </body>' . "\n";
        $xml .= '</doi_batch>' . "\n";

        # re-encode escape sequences that are valid escape sequences in html but
        # not in xml to prevent such sequences from leaking into the xml
        $xml = preg_replace_callback('#&[A-Z0-9]+;#i', function ($matches) {
                return htmlentities(html_entity_decode($matches[0]), ENT_XML1);
            }, $xml);

        # Crossref wants & encoded as amp and not as the equally valid &#038;
        $xml = str_replace('&#038;', '&amp;', $xml);

        return $xml;
    }
}
