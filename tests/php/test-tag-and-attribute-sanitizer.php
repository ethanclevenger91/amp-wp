<?php
/**
 * Class AMP_Tag_And_Attribute_Sanitizer_Test
 *
 * @package AMP
 */

/**
 * Test AMP_Tag_And_Attribute_Sanitizer
 *
 * @covers AMP_Tag_And_Attribute_Sanitizer
 */
class AMP_Tag_And_Attribute_Sanitizer_Test extends WP_UnitTestCase {

	/**
	 * Get data for testing sanitization in the body.
	 *
	 * @return array[] Each array item is a tuple containing pre-sanitized string, sanitized string, and scripts
	 *                 identified during sanitization.
	 */
	public function get_body_data() {
		return [
			'empty_doc'                                    => [
				'',
				'',
			],

			'a-test'                                       => [
				'<a on="tap:see-image-lightbox" role="button" class="button button-secondary play" tabindex="0">Show Image</a>',
			],

			'a4a'                                          => [
				'<amp-ad width="300" height="400" type="fake" data-use-a4a="true" data-vars-analytics-var="bar" src="fake_amp.json"><div placeholder=""></div><div fallback=""></div></amp-ad>',
				null, // No change.
				[ 'amp-ad' ],
			],

			'ads'                                          => [
				'<amp-ad width="300" height="250" type="a9" data-aax_size="300x250" data-aax_pubname="test123" data-aax_src="302"><div placeholder=""></div><div fallback=""></div></amp-ad>',
				null, // No change.
				[ 'amp-ad' ],
			],

			'adsense'                                      => [
				'<amp-ad width="300" height="250" type="adsense" data-ad-client="ca-pub-2005682797531342" data-ad-slot="7046626912"><div placeholder=""></div><div fallback=""></div></amp-ad>',
				null, // No change.
				[ 'amp-ad' ],
			],

			'amp-3q-player'                                => [
				'<amp-3q-player data-id="c8dbe7f4-7f7f-11e6-a407-0cc47a188158" layout="responsive" width="480" height="270"></amp-3q-player>',
				null,
				[ 'amp-3q-player' ],
			],

			'amp-ad'                                       => [
				'<amp-ad width="300" height="250" type="foo"></amp-ad>',
				null, // No change.
				[ 'amp-ad' ],
			],

			'amp-sticky-ad'                                => [
				'<amp-sticky-ad layout="nodisplay"><amp-ad width="320" height="50" type="doubleclick" data-slot="/35096353/amptesting/formats/sticky"></amp-ad></amp-sticky-ad>',
				null,
				[ 'amp-ad', 'amp-sticky-ad' ],
			],

			'amp-sticky-ad-bad-children'                   => [
				'<amp-sticky-ad layout="nodisplay"><span>not allowed</span><amp-ad width="320" height="50" type="doubleclick" data-slot="/35096353/amptesting/formats/sticky"></amp-ad><i>not ok</i></amp-sticky-ad>',
				'',
				[],
			],

			'amp-animation'                                => [
				'<amp-animation layout="nodisplay"><script type="application/json">{}</script></amp-animation>',
				null,
				[ 'amp-animation' ],
			],

			'amp-call-tracking-ok'                         => [
				'<amp-call-tracking config="https://example.com/calltracking.json"><a href="tel:123456789">+1 (23) 456-789</a></amp-call-tracking>',
				null,
				[ 'amp-call-tracking' ],
			],

			'amp-call-tracking-bad-children'               => [
				'<amp-call-tracking config="https://example.com/calltracking.json"><b>bad</b>--and not great: <a href="tel:123456789">+1 (23) 456-789</a><i>more bad</i>not great</amp-call-tracking>',
				'',
				[],
			],

			'amp-call-tracking_blacklisted_config'         => [
				'<amp-call-tracking config="__amp_source_origin"><a href="tel:123456789">+1 (23) 456-789</a></amp-call-tracking>',
				'',
				[], // Important: This needs to be empty because the amp-call-tracking is stripped.
			],

			'amp-embed'                                    => [
				'<amp-embed type="taboola" width="400" height="300" layout="responsive"></amp-embed>',
				null, // No change.
				[ 'amp-ad' ],
			],

			'amp-facebook-comments'                        => [
				'<amp-facebook-comments width="486" height="657" data-href="http://example.com/baz" layout="responsive" data-numposts="5"></amp-facebook-comments>',
				null, // No change.
				[ 'amp-facebook-comments' ],
			],

			'amp-facebook-comments_missing_required_attribute' => [
				'<amp-facebook-comments width="486" height="657" layout="responsive" data-numposts="5"></amp-facebook-comments>',
				'',
				[], // Empty because invalid.
			],

			'amp-facebook-like'                            => [
				'<amp-facebook-like width="90" height="20" data-href="http://example.com/baz" layout="fixed" data-layout="button_count"></amp-facebook-like>',
				null, // No change.
				[ 'amp-facebook-like' ],
			],

			'amp-facebook-like_missing_required_attribute' => [
				'<amp-facebook-like width="90" height="20" layout="fixed" data-layout="button_count"></amp-facebook-like>',
				'',
				[], // Empty because invalid.
			],

			'amp-fit-text'                                 => [
				'<amp-fit-text width="300" height="200" layout="responsive">Lorem ipsum</amp-fit-text>',
				null, // No change.
				[ 'amp-fit-text' ],
			],

			'amp-gist'                                     => [
				'<amp-gist layout="fixed-height" data-gistid="a19" height="1613"></amp-gist>',
				null, // No change.
				[ 'amp-gist' ],
			],

			'amp-gist_missing_mandatory_attribute'         => [
				'<amp-gist layout="fixed-height" height="1613"></amp-gist>',
				'',
				[],
			],

			'amp-iframe'                                   => [
				'<amp-iframe width="600" height="200" sandbox="allow-scripts allow-same-origin" layout="responsive" frameborder="0" src="https://www.example.com"></amp-iframe>',
				null, // No change.
				[ 'amp-iframe' ],
			],

			'amp-iframe_incorrect_protocol'                => [
				'<amp-iframe width="600" height="200" sandbox="allow-scripts allow-same-origin" layout="responsive" frameborder="0" src="masterprotocol://www.example.com"></amp-iframe>',
				'<amp-iframe width="600" height="200" sandbox="allow-scripts allow-same-origin" layout="responsive" frameborder="0"></amp-iframe>',
				[ 'amp-iframe' ],
			],

			'amp-ima-video'                                => [
				'
					<amp-ima-video width="640" height="360" data-tag="https://example.com/foo" layout="responsive" data-src="https://example.com/bar">
						<source src="https://example.com/foo.mp4" type="video/mp4">
						<source src="https://example.com/foo.webm" type="video/webm">
						<track label="English subtitles" kind="subtitles" srclang="en" src="https://example.com/subtitles.vtt">
						<script type="application/json">{"locale": "en","numRedirects": 4}</script>
					</amp-ima-video>
				',
				null, // No change.
				[ 'amp-ima-video' ],
			],

			'amp-ima-video_missing_required_attribute'     => [
				'<amp-ima-video width="640" height="360" layout="responsive" data-src="https://example.com/bar"></amp-ima-video>',
				'',
			],

			'amp-imgur'                                    => [
				'<amp-imgur data-imgur-id="54321" layout="responsive" width="540" height="663"></amp-imgur>',
				null, // No change.
				[ 'amp-imgur' ],
			],

			'amp-install-serviceworker'                    => [
				'<amp-install-serviceworker src="https://www.emample.com/worker.js" data-iframe-src="https://www.example.com/serviceworker.html" layout="nodisplay"></amp-install-serviceworker>',
				null, // No change.
				[ 'amp-install-serviceworker' ],
			],

			'amp-izlesene'                                 => [
				'<amp-izlesene data-videoid="4321" layout="responsive" width="432" height="123"></amp-izlesene>',
				null, // No change.
				[ 'amp-izlesene' ],
			],

			'amp-mathml'                                   => [
				'<amp-mathml layout="container" inline data-formula="\[x = {-b \pm \sqrt{b^2-4ac} \over 2a}.\]"></amp-mathml>',
				null, // No change.
				[ 'amp-mathml' ],
			],

			'amp-riddle-quiz'                              => [
				'<amp-riddle-quiz layout="responsive" width="600" height="400" data-riddle-id="25799"></amp-riddle-quiz>',
				null, // No change.
				[ 'amp-riddle-quiz' ],
			],

			'amp-wistia-player'                            => [
				'<amp-wistia-player data-media-hashed-id="u8p9wq6mq8" width="512" height="360"></amp-wistia-player>',
				null, // No change.
				[ 'amp-wistia-player' ],
			],

			'amp-byside-content'                           => [
				'<amp-byside-content data-webcare-id="D6604AE5D0" data-channel="" data-lang="pt" data-fid="" data-label="amp-number" layout="fixed" width="120" height="40"></amp-byside-content>',
				null, // No change.
				[ 'amp-byside-content' ],
			],

			'amp-bind-macro'                               => [
				'<amp-bind-macro id="circleArea" arguments="radius" expression="3.14 * radius * radius"></amp-bind-macro>',
				null, // No change.
				[ 'amp-bind' ],
			],

			'amp-nexxtv-player'                            => [
				'<amp-nexxtv-player data-mediaid="123ABC" data-client="4321"></amp-nexxtv-player>',
				null, // No change.
				[ 'amp-nexxtv-player' ],
			],

			'amp-playbuzz'                                 => [
				'<amp-playbuzz src="id-from-the-content-here" height="500" data-item-info="true" data-share-buttons="true" data-comments="true"></amp-playbuzz>',
				null, // No change.
				[ 'amp-playbuzz' ],
			],

			'amp-playbuzz_no_src'                          => [
				'<amp-playbuzz height="500" data-item-info="true"></amp-playbuzz>',
				null, // @todo This actually should be stripped because .
				[ 'amp-playbuzz' ],
			],

			'invalid_element_stripped'                     => [
				'<nonexistent><p>Foo text</p><nonexistent>',
				'<p>Foo text</p>',
				[],
			],

			'nested_invalid_elements_stripped'             => [
				'<bad-details><bad-summary><p>Example Summary</p></bad-summary><p>Example expanded text</p></bad-details>',
				'<p>Example Summary</p><p>Example expanded text</p>',
				[],
			],

			// AMP-NEXT-PAGE > [separator].
			'reference-point-amp-next-page-separator'      => [
				'<amp-next-page src="https://example.com/config.json"><div separator><h1>Keep reading</h1></div></amp-next-page>',
				null,
				[ 'amp-next-page' ],
			],

			// amp-next-page extension .json configuration.
			'reference-point-amp-next-page-json-config'    => [
				'<amp-next-page><script type="application/json">{"pages": []}</script></amp-next-page>',
				null,
				[ 'amp-next-page' ],
			],

			'reference-point-amp-carousel-lightbox-exclude' => [
				'<amp-carousel width="400" height="300" layout="responsive" type="slides" lightbox=""><amp-img src="/awesome.png" width="300" height="300" lightbox=""></amp-img><amp-img src="/awesome.png" width="300" height="300" lightbox-exclude=""></amp-img></amp-carousel>',
				null,
				[ 'amp-carousel', 'amp-lightbox-gallery' ],
			],

			'reference-point-lightbox-thumbnail-id'        => [
				'<amp-img src="/awesome.png" width="300" height="300" lightbox lightbox-thumbnail-id="a"></amp-img>',
				null,
				[ 'amp-lightbox-gallery' ],
			],

			'lightbox-with-amp-carousel'                   => [
				'<amp-carousel lightbox width="1600" height="900" layout="responsive" type="slides"><amp-img src="image1" width="200" height="100"></amp-img><amp-img src="image1" width="200" height="100"></amp-img><amp-img src="image1" width="200" height="100"></amp-img></amp-carousel>',
				null,
				[ 'amp-lightbox-gallery', 'amp-carousel' ],
			],

			'reference-points-amp-live-list'               => [
				'<amp-live-list id="my-live-list" data-poll-interval="15000" data-max-items-per-page="20"><button update on="tap:my-live-list.update">You have updates!</button><div items></div><div pagination></div></amp-live-list>',
				null,
				[ 'amp-live-list' ],
			],

			'reference-points-amp-story'                   => call_user_func(
				static function () {
					$html = str_replace(
						[ "\n", "\t" ],
						'',
						'
						<amp-story standalone live-story-disabled supports-landscape title="My Story" publisher="The AMP Team" publisher-logo-src="https://example.com/logo/1x1.png" poster-portrait-src="https://example.com/my-story/poster/3x4.jpg" poster-square-src="https://example.com/my-story/poster/1x1.jpg" poster-landscape-src="https://example.com/my-story/poster/4x3.jpg" background-audio="my.mp3">
							<amp-story-page id="my-first-page">
								<amp-story-grid-layer template="fill">
									<amp-img id="object1" animate-in="rotate-in-left" src="https://example.ampproject.org/helloworld/bg1.jpg" width="900" height="1600">
									</amp-img>
									<!-- Note: The viewbox attribute must currently be lower-case due to https://github.com/ampproject/amp-wp/issues/2045 -->
									<svg viewbox="0 0 100 100"><circle cx="50" cy="50" r="50"></circle></svg>
								</amp-story-grid-layer>
								<amp-story-grid-layer template="vertical">
									<h1 animate-in="fly-in-left" animate-in-duration="0.5s" animate-in-delay="0.4s" animate-in-after="object1">Hello, amp-story!</h1>
									<h2 scale-start="1.0" scale-end="200.1" translate-x="100px" translate-y="200px">Scaled</h2>
									<amp-twitter width="375" height="472" layout="responsive" data-tweetid="885634330868850689"></amp-twitter>
									<amp-twitter interactive width="375" height="472" layout="responsive" data-tweetid="885634330868850689"></amp-twitter>
								</amp-story-grid-layer>
								<amp-pixel src="https://example.com/tracker/foo" layout="nodisplay"></amp-pixel>
							</amp-story-page>
							<amp-story-page id="my-second-page">
								<amp-analytics config="https://example.com/analytics.account.config.json"></amp-analytics>
								<amp-story-grid-layer template="thirds">
									<amp-img grid-area="bottom-third" src="https://example.ampproject.org/helloworld/bg2.gif" width="900" height="1600">
									</amp-img>
								</amp-story-grid-layer>
								<amp-story-grid-layer template="vertical">
									<h1 animate-in="drop" animate-in-delay="500ms" animate-in-duration="600ms">The End</h1>
									<div class="amp-story-block-wrapper">
										<h1 animate-in="drop" animate-in-delay="1500ms" animate-in-duration="700ms">Afterward</h1>
									</div>
									<button class="baddie">bad</button>
								</amp-story-grid-layer>
								<amp-story-cta-layer>
									<a href="https://example.com">Click me.</a>
									<button>Hello</button>
								</amp-story-cta-layer>
								<amp-story-page-attachment layout="nodisplay" theme="dark" data-cta-text="Read more" data-title="My title">
									<h1>My title</h1>
									<p>Lots of interesting text with <a href="https://example.ampproject.org">links</a>!</p>
									<p>More text and a YouTube video!</p>
									<amp-youtube data-videoid="b4Vhdr8jtx0" layout="responsive" width="480" height="270">
									</amp-youtube>
									<amp-video src="https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerJoyrides.mp4" width="300" height="300" controls=""></amp-video>
									<p>And a tweet!</p>
									<amp-twitter data-tweetid="885634330868850689" layout="responsive" width="480" height="270">
									</amp-twitter>
								</amp-story-page-attachment>
							</amp-story-page>
							<amp-story-bookend src="bookendv1.json" layout="nodisplay"></amp-story-bookend>
							<amp-analytics id="75a1fdc3143c" type="googleanalytics"><script type="application/json">{"vars":{"account":"UA-XXXXXX-1"},"triggers":{"trackPageview":{"on":"visible","request":"pageview"}}}</script></amp-analytics>
						</amp-story>
						'
					);

					return [
						$html,
						preg_replace( '#<\w+[^>]*>bad</\w+>#', '', $html ),
						[ 'amp-story', 'amp-analytics', 'amp-twitter', 'amp-youtube', 'amp-video' ],
					];
				}
			),

			'reference-points-bad'                         => [
				'<div lightbox-thumbnail-id update items pagination separator option selected disabled>BAD REFERENCE POINTS</div>',
				'<div>BAD REFERENCE POINTS</div>',
				[],
			],

			'amp-position-observer'                        => [
				'<amp-position-observer intersection-ratios="1"></amp-position-observer>',
				null, // No change.
				[ 'amp-position-observer' ],
			],

			'amp-twitter'                                  => [
				'<amp-twitter width="321" height="543" layout="responsive" data-tweetid="98765"></amp-twitter>',
				null, // No change.
				[ 'amp-twitter' ],
			],

			'amp-user-notification'                        => [
				'<amp-user-notification layout="nodisplay" id="amp-user-notification1" data-show-if-href="https://example.com/api/show?timestamp=TIMESTAMP" data-dismiss-href="https://example.com/api/echo/post">This site uses cookies to personalize content.<a class="btn" on="tap:amp-user-notification1.dismiss">I accept</a></amp-user-notification>',
				'<amp-user-notification layout="nodisplay" id="amp-user-notification1" data-show-if-href="https://example.com/api/show?timestamp=TIMESTAMP" data-dismiss-href="https://example.com/api/echo/post">This site uses cookies to personalize content.<a class="btn" on="tap:amp-user-notification1.dismiss">I accept</a></amp-user-notification>',
				[ 'amp-user-notification' ],
			],

			'amp-video'                                    => [
				'<amp-video width="432" height="987" src="/video/location.mp4"></amp-video>',
				null, // No change.
				[ 'amp-video' ],
			],

			'amp_video_children'                           => [
				'<amp-video width="432" height="987"><track kind="subtitles" src="https://example.com/sampleChapters.vtt" srclang="en"><source src="foo.webm" type="video/webm"><source src="foo.ogg" type="video/ogg"><div placeholder>Placeholder</div><span fallback>Fallback</span></amp-video>',
				null, // No change.
				[ 'amp-video' ],
			],

			'amp_audio_children'                           => [
				'<amp-audio><track kind="subtitles" src="https://example.com/sampleChapters.vtt" srclang="en"><source src="foo.mp3" type="audio/mp3"><source src="foo.wav" type="audio/wav"><div placeholder>Placeholder</div><span fallback>Fallback</span></amp-audio>',
				null, // No change.
				[ 'amp-audio' ],
			],

			'amp-vk'                                       => [
				'<amp-vk width="500" height="300" data-embedtype="post" layout="responsive"></amp-vk>',
				null, // No change.
				[ 'amp-vk' ],
			],

			'amp-apester-media'                            => [
				'<amp-apester-media height="444" data-apester-media-id="57a336dba187a2ca3005e826" layout="fixed-height"></amp-apester-media>',
				'<amp-apester-media height="444" data-apester-media-id="57a336dba187a2ca3005e826" layout="fixed-height"></amp-apester-media>',
				[ 'amp-apester-media' ],
			],

			'button'                                       => [
				'<button on="tap:AMP.setState(foo=\'foo\', isButtonDisabled=true, textClass=\'redBackground\', imgSrc=\'https://ampbyexample.com/img/Shetland_Sheepdog.jpg\', imgSize=200, imgAlt=\'Sheepdog\', videoSrc=\'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerJoyrides.mp4\')">Click me</button>',
				null,
			],

			'brid-player'                                  => [
				'<amp-brid-player data-dynamic="abc" data-partner="264" data-player="4144" data-video="13663" layout="responsive" width="480" height="270"></amp-brid-player>',
				null,
				[ 'amp-brid-player' ],
			],

			'brightcove'                                   => [
				'<amp-brightcove data-account="906043040001" data-video-id="1401169490001" data-player="180a5658-8be8-4f33-8eba-d562ab41b40c" layout="responsive" width="480" height="270"></amp-brightcove>',
				'<amp-brightcove data-account="906043040001" data-video-id="1401169490001" data-player="180a5658-8be8-4f33-8eba-d562ab41b40c" layout="responsive" width="480" height="270"></amp-brightcove>',
				[ 'amp-brightcove' ],
			],

			'carousel_slides'                              => [
				'<amp-carousel width="400" height="300" layout="responsive" type="slides" controls=""><div>hello world</div><amp-img src="https://lh3.googleusercontent.com/pSECrJ82R7-AqeBCOEPGPM9iG9OEIQ_QXcbubWIOdkY=w400-h300-no-n" layout="fill"></amp-img><amp-img src="https://lh3.googleusercontent.com/5rcQ32ml8E5ONp9f9-Rf78IofLb9QjS5_0mqsY1zEFc=w400-h300-no-n" width="400" height="300" layout="responsive"></amp-img><amp-img src="https://lh3.googleusercontent.com/Z4gtm5Bkxyv21Z2PtbTf95Clb9AE4VTR6olbBKYrenM=w400-h300-no-n" width="400" height="300" layout="responsive"></amp-img><amp-soundcloud height="300" layout="fixed-height" data-trackid="243169232"></amp-soundcloud><amp-youtube data-videoid="mGENRKrdoGY" width="400" height="300"></amp-youtube><amp-anim src="https://lh3.googleusercontent.com/qNn8GDz8Jfd-s9lt3Nc4lJeLjVyEaqGJTk1vuCUWazCmAeOBVjSWDD0SMTU7x0zhVe5UzOTKR0n-kN4SXx7yElvpKYvCMaRyS_g-jydhJ_cEVYmYPiZ_j1Y9de43mlKxU6s06uK1NAlpbSkL_046amEKOdgIACICkuWfOBwlw2hUDfjPOWskeyMrcTu8XOEerCLuVqXugG31QC345hz3lUyOlkdT9fMYVUynSERGNzHba7bXMOxKRe3izS5DIWUgJs3oeKYqA-V8iqgCvneD1jj0Ff68V_ajm4BDchQubBJU0ytXVkoWh27ngeEHubpnApOS6fcGsjPxeuMjnzAUtoTsiXz2FZi1mMrxrblJ-kZoAq1DJ95cnoqoa2CYq3BTgq2E8BRe2paNxLJ5GXBCTpNdXMpVJc6eD7ceijQyn-2qanilX-iK3ChbOq0uBHMvsdoC_LsFOu5KzbbNH71vM3DPkvDGmHJmF67Vj8sQ6uBrLnzpYlCyN4-Y9frR8zugDcqX5Q=w400-h300-no" width="400" height="300"><amp-img placeholder="" src="https://lh3.googleusercontent.com/qNn8GDz8Jfd-s9lt3Nc4lJeLjVyEaqGJTk1vuCUWazCmAeOBVjSWDD0SMTU7x0zhVe5UzOTKR0n-kN4SXx7yElvpKYvCMaRyS_g-jydhJ_cEVYmYPiZ_j1Y9de43mlKxU6s06uK1NAlpbSkL_046amEKOdgIACICkuWfOBwlw2hUDfjPOWskeyMrcTu8XOEerCLuVqXugG31QC345hz3lUyOlkdT9fMYVUynSERGNzHba7bXMOxKRe3izS5DIWUgJs3oeKYqA-V8iqgCvneD1jj0Ff68V_ajm4BDchQubBJU0ytXVkoWh27ngeEHubpnApOS6fcGsjPxeuMjnzAUtoTsiXz2FZi1mMrxrblJ-kZoAq1DJ95cnoqoa2CYq3BTgq2E8BRe2paNxLJ5GXBCTpNdXMpVJc6eD7ceijQyn-2qanilX-iK3ChbOq0uBHMvsdoC_LsFOu5KzbbNH71vM3DPkvDGmHJmF67Vj8sQ6uBrLnzpYlCyN4-Y9frR8zugDcqX5Q=w400-h300-no-k" width="400" height="300"></amp-img></amp-anim><amp-audio src="https://ia801402.us.archive.org/16/items/EDIS-SRP-0197-06/EDIS-SRP-0197-06.mp3"></amp-audio><amp-brightcove data-account="906043040001" data-video-id="1401169490001" data-player="180a5658-8be8-4f33-8eba-d562ab41b40c" layout="responsive" width="480" height="270"></amp-brightcove><amp-vimeo data-videoid="27246366" width="500" height="281"></amp-vimeo><amp-dailymotion data-videoid="x3rdtfy" width="500" height="281"></amp-dailymotion><amp-vine data-vineid="MdKjXez002d" width="381" height="381" layout="responsive"></amp-vine><amp-video src="https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerJoyrides.mp4" width="358" height="204" layout="responsive" controls=""></amp-video></amp-carousel><amp-carousel width="auto" height="300" controls=""><div>hello world</div><amp-img src="https://lh3.googleusercontent.com/pSECrJ82R7-AqeBCOEPGPM9iG9OEIQ_QXcbubWIOdkY=w400-h300-no-n" width="400" height="300"></amp-img><amp-img src="https://lh3.googleusercontent.com/5rcQ32ml8E5ONp9f9-Rf78IofLb9QjS5_0mqsY1zEFc=w400-h300-no-n" width="400" height="300"></amp-img><amp-img src="https://lh3.googleusercontent.com/Z4gtm5Bkxyv21Z2PtbTf95Clb9AE4VTR6olbBKYrenM=w400-h300-no-n" width="400" height="300"></amp-img><amp-soundcloud height="300" layout="fixed-height" data-trackid="243169232"></amp-soundcloud><amp-youtube data-videoid="mGENRKrdoGY" width="400" height="300"></amp-youtube><amp-anim src="https://lh3.googleusercontent.com/qNn8GDz8Jfd-s9lt3Nc4lJeLjVyEaqGJTk1vuCUWazCmAeOBVjSWDD0SMTU7x0zhVe5UzOTKR0n-kN4SXx7yElvpKYvCMaRyS_g-jydhJ_cEVYmYPiZ_j1Y9de43mlKxU6s06uK1NAlpbSkL_046amEKOdgIACICkuWfOBwlw2hUDfjPOWskeyMrcTu8XOEerCLuVqXugG31QC345hz3lUyOlkdT9fMYVUynSERGNzHba7bXMOxKRe3izS5DIWUgJs3oeKYqA-V8iqgCvneD1jj0Ff68V_ajm4BDchQubBJU0ytXVkoWh27ngeEHubpnApOS6fcGsjPxeuMjnzAUtoTsiXz2FZi1mMrxrblJ-kZoAq1DJ95cnoqoa2CYq3BTgq2E8BRe2paNxLJ5GXBCTpNdXMpVJc6eD7ceijQyn-2qanilX-iK3ChbOq0uBHMvsdoC_LsFOu5KzbbNH71vM3DPkvDGmHJmF67Vj8sQ6uBrLnzpYlCyN4-Y9frR8zugDcqX5Q=w400-h300-no" width="400" height="300"><amp-img placeholder="" src="https://lh3.googleusercontent.com/qNn8GDz8Jfd-s9lt3Nc4lJeLjVyEaqGJTk1vuCUWazCmAeOBVjSWDD0SMTU7x0zhVe5UzOTKR0n-kN4SXx7yElvpKYvCMaRyS_g-jydhJ_cEVYmYPiZ_j1Y9de43mlKxU6s06uK1NAlpbSkL_046amEKOdgIACICkuWfOBwlw2hUDfjPOWskeyMrcTu8XOEerCLuVqXugG31QC345hz3lUyOlkdT9fMYVUynSERGNzHba7bXMOxKRe3izS5DIWUgJs3oeKYqA-V8iqgCvneD1jj0Ff68V_ajm4BDchQubBJU0ytXVkoWh27ngeEHubpnApOS6fcGsjPxeuMjnzAUtoTsiXz2FZi1mMrxrblJ-kZoAq1DJ95cnoqoa2CYq3BTgq2E8BRe2paNxLJ5GXBCTpNdXMpVJc6eD7ceijQyn-2qanilX-iK3ChbOq0uBHMvsdoC_LsFOu5KzbbNH71vM3DPkvDGmHJmF67Vj8sQ6uBrLnzpYlCyN4-Y9frR8zugDcqX5Q=w400-h300-no-k" width="400" height="300"></amp-img></amp-anim><amp-audio src="https://ia801402.us.archive.org/16/items/EDIS-SRP-0197-06/EDIS-SRP-0197-06.mp3"></amp-audio><amp-brightcove data-account="906043040001" data-video-id="1401169490001" data-player="180a5658-8be8-4f33-8eba-d562ab41b40c" layout="responsive" width="300" height="300"></amp-brightcove><amp-vimeo data-videoid="27246366" width="300" height="300"></amp-vimeo><amp-dailymotion data-videoid="x3rdtfy" width="300" height="300"></amp-dailymotion><amp-vine data-vineid="MdKjXez002d" width="300" height="300"></amp-vine><amp-video src="https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerJoyrides.mp4" width="300" height="300" controls=""></amp-video></amp-carousel>',
				'<amp-carousel width="400" height="300" layout="responsive" type="slides" controls=""><div>hello world</div><amp-img src="https://lh3.googleusercontent.com/pSECrJ82R7-AqeBCOEPGPM9iG9OEIQ_QXcbubWIOdkY=w400-h300-no-n" layout="fill"></amp-img><amp-img src="https://lh3.googleusercontent.com/5rcQ32ml8E5ONp9f9-Rf78IofLb9QjS5_0mqsY1zEFc=w400-h300-no-n" width="400" height="300" layout="responsive"></amp-img><amp-img src="https://lh3.googleusercontent.com/Z4gtm5Bkxyv21Z2PtbTf95Clb9AE4VTR6olbBKYrenM=w400-h300-no-n" width="400" height="300" layout="responsive"></amp-img><amp-soundcloud height="300" layout="fixed-height" data-trackid="243169232"></amp-soundcloud><amp-youtube data-videoid="mGENRKrdoGY" width="400" height="300"></amp-youtube><amp-anim src="https://lh3.googleusercontent.com/qNn8GDz8Jfd-s9lt3Nc4lJeLjVyEaqGJTk1vuCUWazCmAeOBVjSWDD0SMTU7x0zhVe5UzOTKR0n-kN4SXx7yElvpKYvCMaRyS_g-jydhJ_cEVYmYPiZ_j1Y9de43mlKxU6s06uK1NAlpbSkL_046amEKOdgIACICkuWfOBwlw2hUDfjPOWskeyMrcTu8XOEerCLuVqXugG31QC345hz3lUyOlkdT9fMYVUynSERGNzHba7bXMOxKRe3izS5DIWUgJs3oeKYqA-V8iqgCvneD1jj0Ff68V_ajm4BDchQubBJU0ytXVkoWh27ngeEHubpnApOS6fcGsjPxeuMjnzAUtoTsiXz2FZi1mMrxrblJ-kZoAq1DJ95cnoqoa2CYq3BTgq2E8BRe2paNxLJ5GXBCTpNdXMpVJc6eD7ceijQyn-2qanilX-iK3ChbOq0uBHMvsdoC_LsFOu5KzbbNH71vM3DPkvDGmHJmF67Vj8sQ6uBrLnzpYlCyN4-Y9frR8zugDcqX5Q=w400-h300-no" width="400" height="300"><amp-img placeholder="" src="https://lh3.googleusercontent.com/qNn8GDz8Jfd-s9lt3Nc4lJeLjVyEaqGJTk1vuCUWazCmAeOBVjSWDD0SMTU7x0zhVe5UzOTKR0n-kN4SXx7yElvpKYvCMaRyS_g-jydhJ_cEVYmYPiZ_j1Y9de43mlKxU6s06uK1NAlpbSkL_046amEKOdgIACICkuWfOBwlw2hUDfjPOWskeyMrcTu8XOEerCLuVqXugG31QC345hz3lUyOlkdT9fMYVUynSERGNzHba7bXMOxKRe3izS5DIWUgJs3oeKYqA-V8iqgCvneD1jj0Ff68V_ajm4BDchQubBJU0ytXVkoWh27ngeEHubpnApOS6fcGsjPxeuMjnzAUtoTsiXz2FZi1mMrxrblJ-kZoAq1DJ95cnoqoa2CYq3BTgq2E8BRe2paNxLJ5GXBCTpNdXMpVJc6eD7ceijQyn-2qanilX-iK3ChbOq0uBHMvsdoC_LsFOu5KzbbNH71vM3DPkvDGmHJmF67Vj8sQ6uBrLnzpYlCyN4-Y9frR8zugDcqX5Q=w400-h300-no-k" width="400" height="300"></amp-img></amp-anim><amp-audio src="https://ia801402.us.archive.org/16/items/EDIS-SRP-0197-06/EDIS-SRP-0197-06.mp3"></amp-audio><amp-brightcove data-account="906043040001" data-video-id="1401169490001" data-player="180a5658-8be8-4f33-8eba-d562ab41b40c" layout="responsive" width="480" height="270"></amp-brightcove><amp-vimeo data-videoid="27246366" width="500" height="281"></amp-vimeo><amp-dailymotion data-videoid="x3rdtfy" width="500" height="281"></amp-dailymotion><amp-vine data-vineid="MdKjXez002d" width="381" height="381" layout="responsive"></amp-vine><amp-video src="https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerJoyrides.mp4" width="358" height="204" layout="responsive" controls=""></amp-video></amp-carousel><amp-carousel width="auto" height="300" controls=""><div>hello world</div><amp-img src="https://lh3.googleusercontent.com/pSECrJ82R7-AqeBCOEPGPM9iG9OEIQ_QXcbubWIOdkY=w400-h300-no-n" width="400" height="300"></amp-img><amp-img src="https://lh3.googleusercontent.com/5rcQ32ml8E5ONp9f9-Rf78IofLb9QjS5_0mqsY1zEFc=w400-h300-no-n" width="400" height="300"></amp-img><amp-img src="https://lh3.googleusercontent.com/Z4gtm5Bkxyv21Z2PtbTf95Clb9AE4VTR6olbBKYrenM=w400-h300-no-n" width="400" height="300"></amp-img><amp-soundcloud height="300" layout="fixed-height" data-trackid="243169232"></amp-soundcloud><amp-youtube data-videoid="mGENRKrdoGY" width="400" height="300"></amp-youtube><amp-anim src="https://lh3.googleusercontent.com/qNn8GDz8Jfd-s9lt3Nc4lJeLjVyEaqGJTk1vuCUWazCmAeOBVjSWDD0SMTU7x0zhVe5UzOTKR0n-kN4SXx7yElvpKYvCMaRyS_g-jydhJ_cEVYmYPiZ_j1Y9de43mlKxU6s06uK1NAlpbSkL_046amEKOdgIACICkuWfOBwlw2hUDfjPOWskeyMrcTu8XOEerCLuVqXugG31QC345hz3lUyOlkdT9fMYVUynSERGNzHba7bXMOxKRe3izS5DIWUgJs3oeKYqA-V8iqgCvneD1jj0Ff68V_ajm4BDchQubBJU0ytXVkoWh27ngeEHubpnApOS6fcGsjPxeuMjnzAUtoTsiXz2FZi1mMrxrblJ-kZoAq1DJ95cnoqoa2CYq3BTgq2E8BRe2paNxLJ5GXBCTpNdXMpVJc6eD7ceijQyn-2qanilX-iK3ChbOq0uBHMvsdoC_LsFOu5KzbbNH71vM3DPkvDGmHJmF67Vj8sQ6uBrLnzpYlCyN4-Y9frR8zugDcqX5Q=w400-h300-no" width="400" height="300"><amp-img placeholder="" src="https://lh3.googleusercontent.com/qNn8GDz8Jfd-s9lt3Nc4lJeLjVyEaqGJTk1vuCUWazCmAeOBVjSWDD0SMTU7x0zhVe5UzOTKR0n-kN4SXx7yElvpKYvCMaRyS_g-jydhJ_cEVYmYPiZ_j1Y9de43mlKxU6s06uK1NAlpbSkL_046amEKOdgIACICkuWfOBwlw2hUDfjPOWskeyMrcTu8XOEerCLuVqXugG31QC345hz3lUyOlkdT9fMYVUynSERGNzHba7bXMOxKRe3izS5DIWUgJs3oeKYqA-V8iqgCvneD1jj0Ff68V_ajm4BDchQubBJU0ytXVkoWh27ngeEHubpnApOS6fcGsjPxeuMjnzAUtoTsiXz2FZi1mMrxrblJ-kZoAq1DJ95cnoqoa2CYq3BTgq2E8BRe2paNxLJ5GXBCTpNdXMpVJc6eD7ceijQyn-2qanilX-iK3ChbOq0uBHMvsdoC_LsFOu5KzbbNH71vM3DPkvDGmHJmF67Vj8sQ6uBrLnzpYlCyN4-Y9frR8zugDcqX5Q=w400-h300-no-k" width="400" height="300"></amp-img></amp-anim><amp-audio src="https://ia801402.us.archive.org/16/items/EDIS-SRP-0197-06/EDIS-SRP-0197-06.mp3"></amp-audio><amp-brightcove data-account="906043040001" data-video-id="1401169490001" data-player="180a5658-8be8-4f33-8eba-d562ab41b40c" layout="responsive" width="300" height="300"></amp-brightcove><amp-vimeo data-videoid="27246366" width="300" height="300"></amp-vimeo><amp-dailymotion data-videoid="x3rdtfy" width="300" height="300"></amp-dailymotion><amp-vine data-vineid="MdKjXez002d" width="300" height="300"></amp-vine><amp-video src="https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerJoyrides.mp4" width="300" height="300" controls=""></amp-video></amp-carousel>',
				[ 'amp-anim', 'amp-audio', 'amp-brightcove', 'amp-carousel', 'amp-dailymotion', 'amp-soundcloud', 'amp-video', 'amp-vimeo', 'amp-vine', 'amp-youtube' ],
			],

			'carousel_simple'                              => [
				'<amp-carousel width="450" height="300"></amp-carousel>',
				null,
				[ 'amp-carousel' ],
			],

			'carousel_lightbox'                            => [
				'<amp-carousel width="450" height="300" delay="100" arrows [slide]="foo" autoplay loop lightbox></amp-carousel>',
				'<amp-carousel width="450" height="300" delay="100" arrows data-amp-bind-slide="foo" autoplay loop lightbox></amp-carousel>',
				[ 'amp-bind', 'amp-carousel', 'amp-lightbox-gallery' ],
			],

			'base_carousel'                                => [
				'
					<amp-base-carousel width="4" height="3" auto-advance="true" layout="responsive" heights="(min-width: 600px) calc(100% * 4 * 3 / 2), calc(100% * 3 * 3 / 2)" visible-count="(min-width: 600px) 4, 3" advance-count="(min-width: 600px) 4, 3">
						<div lightbox-thumbnail-id="food">first slide</div>
						<div lightbox-exclude>second slide</div>
					</amp-base-carousel>
				',
				null,
				[ 'amp-base-carousel' ],
			],

			'amp-dailymotion'                              => [
				'<amp-dailymotion data-videoid="x3rdtfy" width="500" height="281" dock></amp-dailymotion><h4>Default (responsive)</h4><amp-dailymotion data-videoid="x3rdtfy" width="500" height="281" layout="responsive"></amp-dailymotion><h4>Custom</h4><amp-dailymotion data-videoid="x3rdtfy" data-endscreen-enable="false" data-sharing-enable="false" data-ui-highlight="444444" data-ui-logo="false" data-info="false" width="640" height="360"></amp-dailymotion>',
				null,
				[ 'amp-dailymotion', 'amp-video-docking' ],
			],

			// Try to test for NAME_VALUE_PARENT_DISPATCH.
			'amp_ima_video'                                => [
				'<amp-ima-video width="640" height="360" layout="responsive" data-tag="ads.xml" data-poster="poster.png"><source src="foo.mp4" type="video/mp4"><source src="foo.webm" type="video/webm"><track label="English subtitles" kind="subtitles" srclang="en" src="https://example.com/subtitles.vtt"><script type="application/json">{"locale": "en", "numRedirects": 4}</script></amp-ima-video>',
				null, // No change.
				[ 'amp-ima-video' ],
			],

			// Try to test for NAME_VALUE_DISPATCH.
			'doubleclick-1'                                => [
				'<amp-ad width="480" height="75" type="doubleclick" data-slot="/4119129/mobile_ad_banner" data-multi-size="320x50" class="dashedborder"></amp-ad>',
				'<amp-ad width="480" height="75" type="doubleclick" data-slot="/4119129/mobile_ad_banner" data-multi-size="320x50" class="dashedborder"></amp-ad>',
				[ 'amp-ad' ],
			],

			// Try to test for NAME_DISPATCH.
			'nav_dispatch_key'                             => [
				'<nav><a href="https://example.com">Example</a></nav>',
				null,
			],

			'json_linked_data'                             => [
				'<script type="application/ld+json">{"@context":"http:\/\/schema.org"}</script>',
				null, // No Change.
			],

			'json_linked_data_with_bad_cdata'              => [
				'<script type="application/ld+json"><!-- {"@context":"http:\/\/schema.org"} --></script>',
				'',
			],

			'facebook'                                     => [
				'<amp-facebook width="552" height="303" layout="responsive" data-href="https://www.facebook.com/zuck/posts/10102593740125791"></amp-facebook><h1>More Posts</h1>',
				'<amp-facebook width="552" height="303" layout="responsive" data-href="https://www.facebook.com/zuck/posts/10102593740125791"></amp-facebook><h1>More Posts</h1>',
				[ 'amp-facebook' ],
			],

			'font'                                         => [
				'<amp-font layout="nodisplay" font-family="Comic AMP" timeout="2000"></amp-font><amp-font layout="nodisplay" font-family="Comic AMP Bold" timeout="3000" font-weight="bold"></amp-font>',
				'<amp-font layout="nodisplay" font-family="Comic AMP" timeout="2000"></amp-font><amp-font layout="nodisplay" font-family="Comic AMP Bold" timeout="3000" font-weight="bold"></amp-font>',
				[ 'amp-font' ],
			],

			'form'                                         => [
				'<form method="get" action="/form/search-html/get" target="_blank"><fieldset><label><span>Search for</span><input type="search" placeholder="test" name="term" required></label><input type="submit" value="Search"></fieldset></form>',
				'<form method="get" action="/form/search-html/get" target="_blank"><fieldset><label><span>Search for</span><input type="search" placeholder="test" name="term" required></label><input type="submit" value="Search"></fieldset></form>',
				[ 'amp-form' ],
			],

			'gfycat'                                       => [
				'<amp-gfycat data-gfyid="BareSecondaryFlamingo" width="225" height="400"></amp-gfycat>',
				'<amp-gfycat data-gfyid="BareSecondaryFlamingo" width="225" height="400"></amp-gfycat>',
				[ 'amp-gfycat' ],
			],

			'h2'                                           => [
				'<h2>Example Text</h2>',
			],

			'empty_element'                                => [
				'<br>',
			],

			'merge_two_attr_specs'                         => [
				'<div submit-success>Whatever</div>',
				'<div>Whatever</div>',
			],

			'attribute_value_blacklisted_by_regex_removed' => [
				'<a href="__amp_source_origin">Click me.</a>',
				'<a>Click me.</a>',
			],

			'host_relative_url_allowed'                    => [
				'<a href="/path/to/content">Click me.</a>',
			],

			'protocol_relative_url_allowed'                => [
				'<a href="//example.com/path/to/content">Click me.</a>',
			],

			'node_with_whitelisted_protocol_http_allowed'  => [
				'<a href="http://example.com/path/to/content">Click me.</a>',
			],

			'node_with_whitelisted_protocol_https_allowed' => [
				'<a href="https://example.com/path/to/content">Click me.</a>',
			],

			'node_with_whitelisted_protocol_other_allowed' => [
				implode(
					'',
					[
						'<a href="fb-messenger://example.com/path/to/content">Click me.</a>',
						'<a href="webcal:foo">Click me.</a>',
						'<a href="whatsapp:foo">Click me.</a>',
						'<a href="web+mastodon:follow/@handle@instance">Click me.</a>',
					]
				),
			],

			'node_with_non_parseable_url_removed'          => [
				'<a href="http://foo@">Invalid Link</a>',
				'<a>Invalid Link</a>',
			],

			'node_with_non_parseable_url_leftovers_cleaned_up' => [
				'<a id="this-is-kept" href="http://foo@" target="_blank" download rel="nofollow" rev="nofollow" hreflang="en" type="text/html" class="this-stays">Invalid Link</a>',
				'<a id="this-is-kept" class="this-stays">Invalid Link</a>',
			],

			'attribute_value_valid'                        => [
				'<template type="amp-mustache">Hello {{world}}! <a href="{{user_url}}" title="{{user_name}}">Homepage</a> User content: {{{some_formatting}}} A guy with Mustache: :-{). {{#returning}}Welcome back!{{/returning}} {{^returning}}Welcome for the first time!{{/returning}} </template>',
				null,
				[ 'amp-mustache' ],
			],

			'attribute_value_invalid'                      => [
				// type is mandatory, so the node is removed.
				'<template type="bad-type">Template Data</template>',
				'',
				[], // No scripts because removed.
			],

			'attribute_requirements_overriden_by_placeholders_within_template' => [
				'<template type="amp-mustache"><amp-timeago datetime="{{iso}}"></amp-timeago></template>',
				null,
				[ 'amp-mustache', 'amp-timeago' ],
			],

			'attribute_requirements_overriden_by_placeholders_within_template_newlines' => [
				"<template type=\"amp-mustache\"><amp-timeago datetime=\"first-line\n{{iso}}\"></amp-timeago></template>",
				null,
				[ 'amp-mustache', 'amp-timeago' ],
			],

			'attribute_requirements_not_overriden_by_placeholders_outside_of_template' => [
				'<amp-timeago datetime="{{iso}}"></amp-timeago>',
				'',
			],

			'attribute_requirements_overriden_in_indirect_template_parents' => [
				'<template type="amp-mustache"><div><span><amp-timeago datetime="{{iso}}"></amp-timeago></span></div></template>',
				null,
				[ 'amp-mustache', 'amp-timeago' ],
			],

			'attribute_requirements_not_overriden_in_sibling_template_tags' => [
				'<template type="amp-mustache"></template><amp-timeago datetime="{{iso}}"></amp-timeago>',
				'<template type="amp-mustache"></template>',
				[ 'amp-mustache' ],
			],

			'attribute_amp_accordion_value'                => call_user_func(
				static function() {
					$html = str_replace(
						[ "\n", "\t" ],
						'',
						'
						<amp-accordion class="sample" disable-session-states="">
							ok
							<section expanded>
								<h4>Section 1</h4>
								<p>Bunch of awesome content.</p>
							</section>
							<section>
								<h4>Section 2</h4>
								<div>Bunch of even more awesome content. This time in a <code>&lt;div&gt;</code>.</div>
							</section>
							<section>
								<h4>Section 3</h4>
								<figure>
									<amp-img src="/img/clean-1.jpg" layout="intrinsic" width="400" height="710"></amp-img>
									<figcaption>Images work as well.</figcaption>
								</figure>
							</section>
							ok
						</amp-accordion>
						'
					);

					return [
						$html,
						preg_replace( '#<\w+>bad</\w+>#', '', $html ),
						[ 'amp-accordion' ],
					];
				}
			),

			'attribute_value_with_blacklisted_regex_removed' => [
				'<a rel="import">Click me.</a>',
				'<a>Click me.</a>',
			],

			'attribute_value_with_blacklisted_multi-part_regex_removed' => [
				'<a rel="something else import">Click me.</a>',
				'<a>Click me.</a>',
			],

			'attribute_value_with_required_regex'          => [
				'<a target="_blank">Click me.</a>',
			],

			'attribute_value_with_disallowed_required_regex_removed' => [
				'<a target="_not_blank">Click me.</a>',
				'<a>Click me.</a>',
			],

			'attribute_value_with_required_value_casei_lower' => [
				'<a type="text/html">Click.me.</a>',
			],

			'attribute_value_with_required_value_casei_upper' => [
				'<a type="TEXT/HTML">Click.me.</a>',
			],

			'attribute_value_with_required_value_casei_mixed' => [
				'<a type="TeXt/HtMl">Click.me.</a>',
			],

			'attribute_value_with_bad_value_casei_removed' => [
				'<a type="bad_type">Click.me.</a>',
				'<a>Click.me.</a>',
			],

			'attribute_value_with_value_regex_casei_lower' => [
				'<amp-dailymotion data-videoid="abc"></amp-dailymotion>',
				null, // No change.
				[ 'amp-dailymotion' ],
			],

			'attribute_value_with_value_regex_casei_upper' => [
				'<amp-dailymotion data-videoid="ABC"></amp-dailymotion>',
				null, // No change.
				[ 'amp-dailymotion' ],
			],

			'attribute_value_with_bad_value_regex_casei_removed' => [
				// data-ui-logo should be true|false.
				'<amp-dailymotion data-videoid="123" data-ui-logo="maybe"></amp-dailymotion>',
				'<amp-dailymotion data-videoid="123"></amp-dailymotion>',
				[ 'amp-dailymotion' ],
			],

			'attribute_bad_attr_with_no_value_removed'     => [
				'<amp-ad type="adsense" bad-attr-no-value><div fallback>something here</div></amp-ad>',
				'<amp-ad type="adsense"><div fallback>something here</div></amp-ad>',
				[ 'amp-ad' ],
			],

			'attribute_bad_attr_with_value_removed'        => [
				'<amp-ad type="adsense" bad-attr="some-value">something here</amp-ad>',
				'<amp-ad type="adsense">something here</amp-ad>',
				[ 'amp-ad' ],
			],

			'remove_node_with_invalid_mandatory_attribute' => [
				// script only allows application/json, nothing else.
				'<script type="type/javascript">console.log()</script>',
				'',
			],

			'remove_node_without_mandatory_attribute'      => [
				'<script>console.log()</script>',
				'',
			],

			'remove_script_with_async_attribute'           => [
				'<script async src="//cdn.someecards.com/assets/embed/embed-v1.07.min.js" charset="utf-8"></script>', // phpcs:ignore
				'',
			],

			'remove_invalid_json_script'                   => [
				'<script type="application/json" class="wp-playlist-script">{}</script>',
				'',
			],

			'allow_node_with_valid_mandatory_attribute'    => [
				'<amp-analytics><script type="application/json"></script></amp-analytics>',
				null, // No change.
				[ 'amp-analytics' ],
			],

			'nodes_with_non_whitelisted_tags_replaced_by_children' => [
				'<invalid_tag>this is some text inside the invalid node</invalid_tag>',
				'this is some text inside the invalid node',
			],

			'empty_parent_nodes_of_non_whitelisted_tags_removed' => [
				'<div><span><span><invalid_tag></invalid_tag></span></span></div>',
				'',
			],

			'non_empty_parent_nodes_of_non_whitelisted_tags_removed' => [
				'<div><span><span class="not-empty"><invalid_tag></invalid_tag></span></span></div>',
				'<div><span><span class="not-empty"></span></span></div>',
			],

			'replace_non_whitelisted_node_with_children'   => [
				'<p>This is some text <invalid_tag>with a disallowed tag</invalid_tag> in the middle of it.</p>',
				'<p>This is some text with a disallowed tag in the middle of it.</p>',
			],

			'remove_attribute_on_node_with_missing_mandatory_parent' => [
				'<div submit-success>This is a test.</div>',
				'<div>This is a test.</div>',
			],

			'leave_attribute_on_node_with_present_mandatory_parent' => [
				'<form action-xhr="form.php" method="post" target="_top"><div submit-success>This is a test.</div></form>',
				null,
				[ 'amp-form' ],
			],

			'disallowed_empty_attr_removed'                => [
				'<amp-user-notification data-dismiss-href></amp-user-notification>',
				'<amp-user-notification></amp-user-notification>',
				[ 'amp-user-notification' ],
			],

			'allowed_empty_attr'                           => [
				'<a border=""></a>',
			],

			'remove_node_with_disallowed_ancestor_and_disallowed_child_nodes' => [
				'<amp-sidebar><amp-app-banner>This node is not allowed here.</amp-app-banner><nav><ul><li>Hello</li></ul><ol><li>Hello</li></ol></nav><amp-app-banner>This node is not allowed here.</amp-app-banner></amp-sidebar>',
				'<amp-sidebar><nav><ul><li>Hello</li></ul><ol><li>Hello</li></ol></nav></amp-sidebar>',
				[ 'amp-sidebar' ],
			],

			'amp_story_with_amp_sidebar'                   => [
				str_replace(
					[ "\n", "\t" ],
					'',
					'
						<amp-story standalone title="Stories in AMP - Hello World" publisher="AMP Project" publisher-logo-src="https://ampbyexample.com/favicons/coast-228x228.png" poster-portrait-src="https://ampbyexample.com/img/story_dog2_portrait.jpg">
							<amp-sidebar id="sidebar1" layout="nodisplay">
								<ul>
									<li><a href="https://www.ampproject.org"> External Link </a></li>
									<li>Nav item 2</li>
									<li>Nav item 3</li>
								</ul>
							</amp-sidebar>
							<amp-story-page id="cover">
								<amp-story-grid-layer template="fill">
									<h1>Hello World</h1>
									<p>This is the cover page of this story.</p>
								</amp-story-grid-layer>
							</amp-story-page>
						</amp-story>
					'
				),
				null,
				[ 'amp-sidebar', 'amp-story' ],
			],

			'amp_sidebar_with_autoscroll'                  => [
				str_replace(
					[ "\n", "\t" ],
					'',
					'
						<amp-sidebar id="sidebar1" layout="nodisplay" side="right">
							<nav toolbar="(max-width: 767px)" toolbar-target="target-element">
								<ul>
									<li>Nav item 1</li>
									<li>Nav item 2</li>
									<li>Nav item 3</li>
									<li autoscroll class="currentPage">Nav item 4</li>
									<li>Nav item 5</li>
									<li>Nav item 6</li>
								</ul>
							</nav>
						</amp-sidebar>
						<div id="target-element"></div>
					'
				),
				null,
				[ 'amp-sidebar' ],
			],

			'amp_sidebar_inside_and_outside_div'           => [
				'
				<amp-sidebar id="mobile-sidebar" layout="nodisplay" side="left">
					<nav><a href="/">Home!</a></nav>
				</amp-sidebar>
				<div>
					<amp-sidebar id="mobile-sidebar" layout="nodisplay" side="right">
						<nav>
							<ul>
								<li><a href="https://example.com/"></a></li>
							</ul>
							<div class="some-div">
								...
							</div>
						</nav>
					</amp-sidebar>
				</div>
				',
				null,
				[ 'amp-sidebar' ],
			],

			'remove_node_without_mandatory_ancestor'       => [
				'<div>All I have is this div, when all you want is a noscript tag.<audio>Sweet tunes</audio></div>',
				'<div>All I have is this div, when all you want is a noscript tag.</div>',
			],

			'amp-img_with_good_protocols'                  => [
				'<amp-img src="https://example.com/resource1" srcset="https://example.com/resource1 320w, https://example.com/resource2 480w"></amp-img>',
			],

			'amp-img_with_relative_urls_containing_colons' => [
				'<amp-img src="/winning:yes.jpg" width="100" height="200"></amp-img>',
			],

			'allowed_tag_only'                             => [
				'<p>Text</p><img src="/path/to/file.jpg">',
				'<p>Text</p>',
			],

			'disallowed_attributes'                        => [
				'<a href="/path/to/file.jpg" style="border: 1px solid red !important;">Link</a>',
				'<a href="/path/to/file.jpg">Link</a>',
			],

			'onclick_attribute'                            => [
				'<a href="/path/to/file.jpg" onclick="alert(e);">Link</a>',
				'<a href="/path/to/file.jpg">Link</a>',
			],

			'on_attribute'                                 => [
				'<button on="tap:my-lightbox">Tap Me</button>',
			],

			'multiple_disallowed_attributes'               => [
				'<a href="/path/to/file.jpg" style="border: 1px solid red !important;" onclick="alert(e);">Link</a>',
				'<a href="/path/to/file.jpg">Link</a>',
			],

			'attribute_recursive'                          => [
				'<div style="border: 1px solid red !important;"><a href="/path/to/file.jpg" onclick="alert(e);">Hello World</a></div>',
				'<div><a href="/path/to/file.jpg">Hello World</a></div>',
			],

			'no_strip_amp_tags'                            => [
				'<amp-img src="http://example.com/path/to/file.jpg" width="300" height="300"></amp-img>',
			],

			'a_with_attachment_rel'                        => [
				'<a href="http://example.com" rel="wp-att-1686">Link</a>',
			],

			'a_with_invalid_name'                          => [
				'<a name=shadowRoot>Shadow Root!</a>',
				'<a>Shadow Root!</a>',
			],

			'a_with_attachment_rel_plus_another_valid_value' => [
				'<a href="http://example.com" rel="attachment wp-att-1686">Link</a>',
			],

			'a_with_rev'                                   => [
				'<a href="http://example.com" rev="footnote">Link</a>',
			],

			'a_with_target_blank'                          => [
				'<a href="http://example.com" target="_blank">Link</a>',
			],

			'a_with_target_uppercase_blank'                => [
				'<a href="http://example.com" target="_BLANK">Link</a>',
				'<a href="http://example.com">Link</a>',
			],

			'a_with_target_new'                            => [
				'<a href="http://example.com" target="_new">Link</a>',
				'<a href="http://example.com">Link</a>',
			],

			'a_with_target_self'                           => [
				'<a href="http://example.com" target="_self">Link</a>',
			],

			'a_with_target_invalid'                        => [
				'<a href="http://example.com" target="boom">Link</a>',
				'<a href="http://example.com">Link</a>',
			],

			'a_with_href_invalid'                          => [
				'<a href="some%20random%20text">Link</a>',
			],

			'a_with_href_scheme_tel'                       => [
				'<a href="tel:4166669999">Call Me, Maybe</a>',
			],

			'a_with_href_scheme_sms'                       => [
				'<a href="sms:4166669999">SMS Me, Maybe</a>',
			],

			'a_with_href_scheme_mailto'                    => [
				'<a href="mailto:email@example.com">Email Me, Maybe</a>',
			],

			'a_with_href_relative'                         => [
				'<a href="/home">Home</a>',
			],

			'a_with_anchor'                                => [
				'<a href="#section2">Home</a>',
			],

			'a_is_anchor'                                  => [
				'<a name="section2"></a>',
			],

			'a_is_achor_with_id'                           => [
				'<a id="section3"></a>',
			],

			'a_empty'                                      => [
				'<a>Hello World</a>',
			],

			'a_empty_with_children_with_restricted_attributes' => [
				'<a><span style="color: red !important;">Red</span>&amp;<span style="color: blue !important;">Orange</span></a>',
				'<a><span>Red</span>&amp;<span>Orange</span></a>',
			],

			'spans_with_xml_namespaced_attributes'         => [
				'<p><span lang="es" xml:lang="es">hola</span><span xml:space="preserve">mundo</span></p>',
				'<p><span lang="es">hola</span><span>mundo</span></p>',
			],

			'h1_with_size'                                 => [
				'<h1 size="1">Headline</h1>',
				'<h1>Headline</h1>',
			],

			'font_tag'                                     => [
				'<font size="1">Headline</font>',
				'Headline',
			],

			'span_with_custom_attr'                        => [
				'<span class="foo" custom="not-allowed">value</span>',
				'<span class="foo">value</span>',
			],

			'a_with_custom_protocol'                       => [
				'<a class="foo" href="custom:bad">value</a>',
				'<a class="foo">value</a>',
			],

			'a_with_wrong_host'                            => [
				'<a class="foo" href="http://foo bar">value</a>',
				'<a class="foo">value</a>',
			],
			'a_with_encoded_host'                          => [
				'<a class="foo" href="http://%65%78%61%6d%70%6c%65%2e%63%6f%6d/foo/">value</a>',
				null,
			],
			'a_with_wrong_schemeless_host'                 => [
				'<a class="foo" href="//bad domain with a space.com/foo">value</a>',
				'<a class="foo">value</a>',
			],
			'a_with_mail_host'                             => [
				'<a class="foo" href="mail to:foo@bar.com">value</a>',
				'<a class="foo">value</a>',
			],

			// font is removed so we should check that other elements are checked as well.
			'font_with_other_bad_elements'                 => [
				'<font size="1">Headline</font><span style="color: blue !important">Span</span>',
				'Headline<span>Span</span>',
			],

			'amp_bind_attr'                                => [
				'<p [text]="\'Hello \' + foo">Hello World</p><button on="tap:AMP.setState({foo: \'amp-bind\'})">Update</button>',
				'<p data-amp-bind-text="\'Hello \' + foo">Hello World</p><button on="tap:AMP.setState({foo: \'amp-bind\'})">Update</button>',
				[ 'amp-bind' ],
			],

			'data_amp_bind_attr'                           => [
				'<p data-amp-bind-text="\'Hello \' + foo">Hello World</p><button on="tap:AMP.setState({foo: \'amp-bind\'})">Update</button>',
				null,
				[ 'amp-bind' ],
			],

			'amp_bind_with_greater_than_symbol'            => [
				'<div class="home page-template-default page page-id-7 logged-in wp-custom-logo group-blog" [class]="minnow.bodyClasses.concat( minnow.navMenuExpanded ? \'sidebar-open\' : \'\' ).filter( className => \'\' != className )">hello</div>',
				'<div class="home page-template-default page page-id-7 logged-in wp-custom-logo group-blog" data-amp-bind-class="minnow.bodyClasses.concat( minnow.navMenuExpanded ? \'sidebar-open\' : \'\' ).filter( className =&gt; \'\' != className )">hello</div>',
				[ 'amp-bind' ],
			],

			'amp_bad_bind_attr'                            => [
				'<a [href]=\'/\' [hidden]>test</a><p [text]="\'Hello \' + name" [unrecognized] title="Foo"><button [disabled]="" [type]=\'\'>Hello World</button></p>',
				'<a data-amp-bind-href="/" data-amp-bind-hidden>test</a><p data-amp-bind-text="\'Hello \' + name" title="Foo"><button data-amp-bind-disabled="" data-amp-bind-type="">Hello World</button></p>',
				[ 'amp-bind' ],
			],

			'amp-state'                                    => [
				'<amp-state id="someNumber"><script type="application/json">4</script></amp-state>',
				null,
				[ 'amp-bind' ],
			],

			'amp-state-bad'                                => [
				'<amp-state id="someNumber"><i>bad</i><script type="application/json">4</script></amp-state>',
				'',
				[],
			],

			'amp-state-src'                                => [
				'<amp-state id="myRemoteState" src="https://data.com/articles.json"></amp-state>',
				null,
				[ 'amp-bind' ],
			],

			// Adapted from <https://www.ampproject.org/docs/reference/components/amp-selector>.
			'reference-points-amp_selector_and_carousel_with_boolean_attributes' => [
				str_replace(
					[ "\n", "\t" ],
					'',
					'
					<form action="/" method="get" target="_blank" id="form1">
						<amp-selector layout="container" name="single_image_select">
							<ul>
								<li>
									<amp-img src="/img1.png" width="50" height="50" option="1"></amp-img>
								</li>
								<li>
									<amp-img src="/img2.png" width="50" height="50" option="2" disabled></amp-img>
								</li>
								<li option="na" selected>None of the Above</li>
							</ul>
						</amp-selector>
						<amp-selector layout="container" name="multi_image_select" multiple>
							<amp-img src="/img1.png" width="50" height="50" option="1"></amp-img>
							<amp-img src="/img2.png" width="50" height="50" option="2"></amp-img>
							<amp-img src="/img3.png" width="50" height="50" option="3"></amp-img>
						</amp-selector>
						<amp-selector layout="container" name="multi_image_select_1" multiple>
							<amp-carousel id="carousel-1" width="200" height="60" controls>
								<amp-img src="/img1.png" width="80" height="60" option="a"></amp-img>
								<amp-img src="/img2.png" width="80" height="60" option="b" selected></amp-img>
								<amp-img src="/img3.png" width="80" height="60" option="c"></amp-img>
								<amp-img src="/img4.png" width="80" height="60" option="d" disabled></amp-img>
							</amp-carousel>
						</amp-selector>
					</form>
					<amp-selector layout="container" name="multi_image_select_2" multiple form="form1">
						<amp-carousel id="carousel-1" width="400" height="300" type="slides" controls>
							<amp-img src="/img1.png" width="80" height="60" option="a"></amp-img>
							<amp-img src="/img2.png" width="80" height="60" option="b" selected></amp-img>
							<amp-img src="/img3.png" width="80" height="60" option="c"></amp-img>
							<amp-img src="/img4.png" width="80" height="60" option="d"></amp-img>
						</amp-carousel>
					</amp-selector>
					'
				),
				null, // No change.
				[ 'amp-selector', 'amp-form', 'amp-carousel' ],
			],

			'amp_live_list_sort'                           => [
				'<amp-live-list sort="ascending" data-poll-interval="15000" data-max-items-per-page="5" id="amp-live-list-insert-blog"><button update on="tap:amp-live-list-insert-blog.update" class="ampstart-btn ml1 caps">You have updates</button><div items><div id="A green landscape with trees." data-sort-time="20180317225019">Hello</div></div></amp-live-list>',
				null, // No change.
				[ 'amp-live-list' ],
			],

			'amp_consent'                                  => [
				'<amp-consent media="all" noloading></amp-consent>',
				null, // No change.
				[ 'amp-consent' ],
			],

			'amp_date_picker'                              => [
				'<amp-date-picker id="simple-date-picker" type="single" mode="overlay" layout="container" on="select:AMP.setState({date1: event.date, dateType1: event.id})" format="Y-MM-DD" open-after-select input-selector="[name=date1]" class="mr1 ml1 flex picker"><div class="ampstart-input inline-block mt1"><input class="border-none p0" name="date1" placeholder="Pick a date"></div><button class="ampstart-btn m1 caps" on="tap: simple-date-picker.clear">Clear</button></amp-date-picker>',
				null, // No change.
				[ 'amp-date-picker' ],
			],

			'amp_date_picker_range'                        => [
				'<amp-date-picker type="range" minimum-nights="2" maximum-nights="4" mode="overlay" id="range-date-picker" on=" select: AMP.setState({ dates: event.dates, startDate: event.start, endDate: event.end })" format="YYYY-MM-DD" open-after-select min="2017-10-26" start-input-selector="#range-start" end-input-selector="#range-end" class="example-picker space-between"><div class="ampstart-input"><input class="border-none p0" id="range-start" placeholder="Start date"></div><div class="ampstart-input"><input class="border-none p0" id="range-end" placeholder="End date"></div><button class="ampstart-btn caps" on="tap:range-date-picker.clear">Clear</button><template type="amp-mustache" info-template><span [text]="(startDate &amp;&amp; endDate ? \'You picked \' + startDate.date + \' as start date and \' + endDate.date + \' as end date.\' : \'You will see your chosen dates here.\')"> You will see your chosen dates here.</span></template></amp-date-picker>',
				'<amp-date-picker type="range" minimum-nights="2" maximum-nights="4" mode="overlay" id="range-date-picker" on=" select: AMP.setState({ dates: event.dates, startDate: event.start, endDate: event.end })" format="YYYY-MM-DD" open-after-select min="2017-10-26" start-input-selector="#range-start" end-input-selector="#range-end" class="example-picker space-between"><div class="ampstart-input"><input class="border-none p0" id="range-start" placeholder="Start date"></div><div class="ampstart-input"><input class="border-none p0" id="range-end" placeholder="End date"></div><button class="ampstart-btn caps" on="tap:range-date-picker.clear">Clear</button><template type="amp-mustache" info-template><span data-amp-bind-text="(startDate &amp;&amp; endDate ? \'You picked \' + startDate.date + \' as start date and \' + endDate.date + \' as end date.\' : \'You will see your chosen dates here.\')"> You will see your chosen dates here.</span></template></amp-date-picker>',
				[ 'amp-date-picker', 'amp-bind', 'amp-mustache' ],
			],

			'amp-delight-player'                           => [
				'<amp-delight-player data-content-id="-987521" layout="responsive" width="400" height="300"></amp-delight-player>',
				null, // No change.
				[ 'amp-delight-player' ],
			],

			'amp-img-layout-allowed'                       => [
				implode(
					'',
					[
						'<amp-img src="/img1.png" width="50" height="50" layout="fill"></amp-img>',
						'<amp-img src="/img1.png" width="50" height="50" layout="fixed"></amp-img>',
						'<amp-img src="/img1.png" width="50" height="50" layout="fixed-height"></amp-img>',
						'<amp-img src="/img1.png" width="50" height="50" layout="flex-item"></amp-img>',
						'<amp-img src="/img1.png" width="50" height="50" layout="intrinsic"></amp-img>',
						'<amp-img src="/img1.png" width="50" height="50" layout="nodisplay"></amp-img>',
						'<amp-img src="/img1.png" width="50" height="50" layout="responsive"></amp-img>',
					]
				),
				null, // No change.
				[],
			],

			'amp-img-layout-illegal'                       => [
				'<amp-img src="/img1.png" width="50" height="50" layout="container"></amp-img>',
				'<amp-img src="/img1.png" width="50" height="50"></amp-img>',
				[],
			],

			'amp-img-layout-unknown'                       => [
				'<amp-img src="/img1.png" width="50" height="50" layout="bogus-value"></amp-img>',
				'<amp-img src="/img1.png" width="50" height="50"></amp-img>',
				[],
			],

			'non-layout-span-element-attrs'                => [
				'<span id="test" width="1" height="1" heights="(min-width:500px) 200px, 80%" sizes="(min-width: 650px) 50vw, 100vw" layout="nodisplay" [height]="1" [width]="1">Test</span>',
				'<span id="test">Test</span>',
				[],
			],

			'non-layout-col-element-attrs'                 => [
				'<table><col class="foo" width="123" style="background:red !important;"><col class="bar" style="background:green !important;" width="12%"><col class="baz" style="background:blue !important;" width="2*"><tr><td>1</td><td>2</td><td>3</td></tr></table>',
				'<table><col class="foo"><col class="bar"><col class="baz"><tr><td>1</td><td>2</td><td>3</td></tr></table>',
				[],
			],

			'amp-geo'                                      => [
				'<amp-geo layout="nodisplay"><script type="application/json">{ "AmpBind": true, "ISOCountryGroups": { "nafta": [ "ca", "mx", "us", "unknown" ], "waldo": [ "unknown" ], "anz": [ "au", "nz" ] } }</script></amp-geo>',
				null,
				[ 'amp-geo' ],
			],

			'amp-geo-bad-children'                         => [
				'<amp-geo layout="nodisplay"><div>bad</div><script type="application/json">{ "AmpBind": true, "ISOCountryGroups": { "nafta": [ "ca", "mx", "us", "unknown" ], "waldo": [ "unknown" ], "anz": [ "au", "nz" ] } }</script></amp-geo>',
				'',
				[],
			],

			'amp-addthis-valid'                            => [
				'
					<amp-addthis
					  width="320"
					  height="92"
					  data-pub-id="ra-59c2c366435ef478"
					  data-widget-id="0fyg">
					</amp-addthis>
				',
				null,
				[ 'amp-addthis' ],
			],

			'amp-addthis-responsive-layout'                => [
				'
					<amp-addthis
					  width="320"
					  height="92"
					  layout="responsive"
					  data-pub-id="ra-59c3d23bf51957fd"
					  data-widget-id="o2x1">
					</amp-addthis>
				',
				null,
				[ 'amp-addthis' ],
			],

			'amp-addthis-custom-share-attributes'          => [
				'
					<amp-addthis
					  width="320"
					  height="92"
					  data-pub-id="ra-59c2c366435ef478"
					  data-widget-id="0fyg"
					  data-share-title="This Title Will Be Shared"
					  data-share-url="https://www.addthis.com"
					  data-share-media="https://i.imgur.com/yNlQWRM.jpg"
					  data-share-description="This is the description that will be shared.">
					</amp-addthis>
				',
				null,
				[ 'amp-addthis' ],
			],

			'amp-addthis-wordpress-mode'                   => [
				'
					<!-- AddThis WordPress Mode -->
					<amp-addthis
					  width="320"
					  height="92"
					  data-pub-id="ra-5c1a9eed18daaf81"
					  data-class-name="at-above-post"
					  data-widget-id="g7wl">
					</amp-addthis>
				',
				null,
				[ 'amp-addthis' ],
			],

			'amp-addthis-wordpress-mode-no-render-without-class-name' => [
				'
					<amp-addthis
					  width="320"
					  height="92"
					  data-pub-id="ra-5c1a9eed18daaf81"
					  data-class-name=""
					  data-widget-id="g7wl">
					</amp-addthis>
				',
				null,
				[ 'amp-addthis' ],
			],

			'amp-addthis-inline-using-widget-id'           => [
				'
					<amp-addthis
					  width="320"
					  height="92"
					  data-pub-id="ra-5adf5f2869f63c7c"
					  data-widget-id="1o1v">
					</amp-addthis>
				',
				null,
				[ 'amp-addthis' ],
			],

			'amp-addthis-inline-using-product-code'        => [
				'
					<amp-addthis
					  width="320"
					  height="92"
					  data-pub-id="ra-5adf5f2869f63c7c"
					  data-product-code="shin">
					</amp-addthis>
				',
				null,
				[ 'amp-addthis' ],
			],

			'amp-addthis-floating-using-product-code'      => [
				'
					<amp-addthis
					  width="320"
					  height="92"
					  data-pub-id="ra-5adf5ec1cb6be565"
					  data-widget-type="floating"
					  data-product-code="shfs">
					</amp-addthis>
				',
				null,
				[ 'amp-addthis' ],
			],

			'amp-addthis-with-invalid-attribute'           => [
				'<amp-addthis width="320" height="240" data-pub-id="ra-5adf5f2869f63c7c" data-product-code="shin" data-share-url="mailto:foo@example.com"></amp-addthis>',
				'<amp-addthis width="320" height="240" data-pub-id="ra-5adf5f2869f63c7c" data-product-code="shin" data-share-url=""></amp-addthis>',
				[ 'amp-addthis' ],
			],

			'amp-3d-gltf'                                  => [
				'<amp-3d-gltf layout="responsive" width="320" height="240" alpha="true" antialiasing="true" src="path/to/model.glb"></amp-3d-gltf>',
				null,
				[ 'amp-3d-gltf' ],
			],

			'amp-date-countdown'                           => [
				'<amp-date-countdown timestamp-seconds="2147483648" layout="fixed-height" height="50"><template type="amp-mustache"><p class="p1"> {{d}} days, {{h}} hours, {{m}} minutes and {{s}} seconds until <a href="https://en.wikipedia.org/wiki/Year_2038_problem">Y2K38</a>.</p></template></amp-date-countdown>',
				null,
				[ 'amp-date-countdown', 'amp-mustache' ],
			],

			'amp-google-document-embed'                    => [
				'<amp-google-document-embed src="https://www.example.com/document.pdf" width="800" height="600" layout="responsive"></amp-google-document-embed>',
				null,
				[ 'amp-google-document-embed' ],
			],

			'amp-orientation-observer'                     => [
				'<amp-orientation-observer on="beta:clockAnim1.seekTo(percent=event.percent)" layout="nodisplay"></amp-orientation-observer>',
				null,
				[ 'amp-orientation-observer' ],
			],

			'amp-pan-zoom'                                 => [
				'<amp-layout layout="responsive" width="4" height="3"><amp-pan-zoom layout="fill" disable-double-tap><svg focusable="false"> ... </svg></amp-pan-zoom></amp-layout>',
				null,
				[ 'amp-pan-zoom' ],
			],

			'amp-yotpo'                                    => [
				'<amp-yotpo width="550" height="700" layout="responsive" data-app-key="liSBkl621ZZsb88tsckAs6Bzx6jQeTJTv8CDf8y5" data-widget-type="MainWidget" data-product-id="9408616206" data-name="hockey skates" data-url="https://ranabram.myshopify.com/products/hockey-skates" data-image-url="https://ichef.bbci.co.uk/news/320/media/images/83351000/jpg/_83351965_explorer273lincolnshirewoldssouthpicturebynicholassilkstone.jpg" data-descriptipn="skates" data-yotpo-element-id="1"></amp-yotpo>',
				null,
				[ 'amp-yotpo' ],
			],

			'amp-embedly'                                  => [
				'<amp-embedly-key value="12af2e3543ee432ca35ac30a4b4f656a" layout="nodisplay"></amp-embedly-key><amp-embedly-card data-url="https://twitter.com/AMPhtml/status/986750295077040128" layout="responsive" width="150" height="80" data-card-theme="dark" data-card-controls="0"></amp-embedly-card>',
				null,
				[ 'amp-embedly-card' ],
			],

			'amp-lightbox'                                 => [
				'<amp-lightbox id="my-lightbox" [open]="true" animate-in="fly-in-top" layout="nodisplay"><div class="lightbox" on="tap:my-lightbox.close" role="button" tabindex="0"><h1>Hello World!</h1></div></amp-lightbox>',
				'<amp-lightbox id="my-lightbox" data-amp-bind-open="true" animate-in="fly-in-top" layout="nodisplay"><div class="lightbox" on="tap:my-lightbox.close" role="button" tabindex="0"><h1>Hello World!</h1></div></amp-lightbox>',
				[ 'amp-lightbox', 'amp-bind' ],
			],

			'amp-form-messages'                            => [
				'<form action-xhr="https://example.com/" method="post"><fieldset><input type="text" name="do-not-verify" no-verify><input type="text" name="firstName"></fieldset><div verify-error=""><template type="amp-mustache">There is a mistake in the form!{{#verifyErrors}}{{message}}{{/verifyErrors}}</template></div><div submitting=""><template type="amp-mustache">Form submitting... Thank you for waiting {{name}}.</template></div><div submit-success=""><template type="amp-mustache">Success! Thanks {{name}} for subscribing! Please make sure to check your email {{email}}to confirm! After that we\'ll start sending you weekly articles on {{#interests}}<b>{{name}}</b> {{/interests}}.</template></div><div submit-error><template type="amp-mustache">Oops! {{name}}, {{message}}.</template></div></form>',
				null,
				[ 'amp-form', 'amp-mustache' ],
			],

			'amp-input-mask'                               => [
				'<form method="post" class="p2" action-xhr="/components/amp-inputmask/postal" target="_top"><label>Postal code: <input name="code" mask="L0L_0L0" mask-trim-zeros="3" placeholder="A1A 1A1"></label><input type="submit"><div submit-success><template type="amp-mustache"><p>You submitted: {{code}}</p></template></div></form>',
				null,
				[ 'amp-form', 'amp-inputmask', 'amp-mustache' ],
			],

			'amp_textarea_without_autoexpand'              => [
				'<textarea name="without-autoexpand"></textarea>',
				null,
				[],
			],

			'amp_textarea_with_autoexpand_and_defaulttext' => [
				'<textarea name="with-autoexpand" autoexpand [defaulttext]="hello" [text]="goodbye">hello</textarea>',
				'<textarea name="with-autoexpand" autoexpand data-amp-bind-defaulttext="hello" data-amp-bind-text="goodbye">hello</textarea>',
				[ 'amp-form', 'amp-bind' ],
			],

			'amp-viqeo-player'                             => [
				'<amp-viqeo-player data-profileid="184" data-videoid="b51b70cdbb06248f4438" width="640" height="360" layout="responsive"></amp-viqeo-player>',
				null,
				[ 'amp-viqeo-player' ],
			],

			'amp-image-slider'                             => [
				'<amp-image-slider layout="responsive" width="100" height="200"><amp-img src="/green-apple.jpg" alt="A green apple"></amp-img><amp-img src="/red-apple.jpg" alt="A red apple"></amp-img><div first>This apple is green</div><div second>This apple is red</div></amp-image-slider>',
				null,
				[ 'amp-image-slider' ],
			],

			'amp-image-slider-bad-children'                => [
				'<amp-image-slider layout="responsive" width="100" height="200"><amp-img src="/green-apple.jpg" alt="A green apple"></amp-img></amp-image-slider>',
				'',
				[],
			],

			'amp-image-slider-more-bad-children'           => [
				'<amp-image-slider layout="responsive" width="100" height="200"><span>Not allowed</span><amp-img src="/green-apple.jpg" alt="A green apple"></amp-img><i>forbidden</i><amp-img src="/red-apple.jpg" alt="A red apple"></amp-img><div first>This apple is green</div><strong>not allowed</strong><div second>This apple is red</div><i>not</i> <span>ok</span></amp-image-slider>',
				'',
				[],
			],

			'amp-fx-collection'                            => [
				'<h1 amp-fx="parallax" data-parallax-factor="1.5">A title that moves faster than other content.</h1>',
				null,
				[ 'amp-fx-collection' ],
			],

			'amp-date-display'                             => [
				'<amp-date-display datetime="2017-08-02T15:05:05.000" layout="fixed" width="360" height="20"><template type="amp-mustache"><div>{{dayName}} {{day}} {{monthName}} {{year}} {{hourTwoDigit}}:{{minuteTwoDigit}}:{{secondTwoDigit}}</div></template></amp-date-display>',
				null,
				[ 'amp-date-display', 'amp-mustache' ],
			],

			'amp_date_display_template'                    => [
				'
				<amp-date-display template="dateTime" locale="pt-br" datetime="now" layout="flex-item"> </amp-date-display>
				<template type="amp-mustache" id="dateTime">
					{{day}} de {{monthName}} {{year}}<small class="fg-muted block">{{hourTwoDigit}}:{{minuteTwoDigit}}</small>
				</template>
				',
				null, // No change.
				[ 'amp-date-display', 'amp-mustache' ],
			],

			'amp-list'                                     => [
				'<amp-list credentials="include" src="https://example.com/json/product.json?clientId=CLIENT_ID(myCookieId)"><template type="amp-mustache">Your personal offer: ${{price}}</template></amp-list>',
				null,
				[ 'amp-list', 'amp-mustache' ],
			],

			'amp-list-load-more'                           => [
				str_replace(
					[ "\n", "\t" ],
					'',
					'
						<amp-list load-more="auto" src="https://www.load.more.example.com/" width="400" height="800">
							<amp-list-load-more load-more-button>
								<template type="amp-mustache">
									Showing {{#count}} out of {{#total}} items
									<button>Click here to see more!</button>
								</template>
							</amp-list-load-more>
							<amp-list-load-more load-more-failed>
								<div>
									Here is some unclickable text saying sorry loading failed.
								</div>
								<button load-more-clickable>Click me to reload!</button>
							</amp-list-load-more>
							<amp-list-load-more load-more-loading>
								<svg>...</svg>
							</amp-list-load-more>
							<amp-list-load-more load-more-failed>
								<button>Unable to Load More</button>
							</amp-list-load-more>
							<amp-list-load-more load-more-end>
								Congratulations! You reached the end.
							</amp-list-load-more>
							<div fetch-error align="center">
								Fetch error!
							</div>
							<div placeholder>
								Placeholder!
							</div>
							<div fallback>
								Fallback!
							</div>
						</amp-list>
					'
				),
				null,
				[ 'amp-list', 'amp-mustache' ],
			],

			'amp-recaptcha-input'                          => [
				'<form action-xhr="/" target="_top" method="post"><amp-recaptcha-input layout="nodisplay" name="reCAPTCHA_body_key" data-sitekey="reCAPTCHA_site_key" data-action="reCAPTCHA_example_action"></amp-recaptcha-input></form>',
				null,
				[ 'amp-form', 'amp-recaptcha-input' ],
			],

			// @todo The poster should not be allowed if there is a placeholder.
			'amp-video-iframe'                             => [
				'<amp-video-iframe src="https://example.com/video/" width="500" height="500" poster="https://example.com/poster.jpg" autoplay dock implements-media-session implements-rotate-to-fullscreen referrerpolicy></amp-video-iframe>',
				null,
				[ 'amp-video-iframe', 'amp-video-docking' ],
			],

			'amp-youtube'                                  => [
				'<amp-youtube id="myLiveChannel" loop data-live-channelid="UCB8Kb4pxYzsDsHxzBfnid4Q" width="358" height="204" layout="responsive" dock><amp-img src="https://i.ytimg.com/vi/Wm1fWz-7nLQ/hqdefault_live.jpg" placeholder layout="fill"></amp-img></amp-youtube>',
				null,
				[ 'amp-youtube', 'amp-video-docking' ],
			],

			'details'                                      => [
				'<details open [open]="foo.state"><summary>Learn more</summary><p>You are educated</p></details>',
				'<details open data-amp-bind-open="foo.state"><summary>Learn more</summary><p>You are educated</p></details>',
				[ 'amp-bind' ],
			],

			'amp-plain-text-script-template'               => [
				'<script type="text/plain" template="amp-mustache">Hello {{world}}!</script>',
				null,
				[ 'amp-mustache' ],
			],

			'amp-action-macro'                             => [
				// @todo Should calling AMP.setState() automatically cause the amp-bind extension to be added?
				'
					<amp-action-macro id="closeNavigations" execute="AMP.setState({nav1: \'close\', nav2: \'close})"></amp-action-macro>
					<button on="tap:closeNavigations.execute()">Close all</button>
					<div on="tap:closeNavigations.execute()">Close all</div>
				',
				null,
				[ 'amp-action-macro' ],
			],

			'amp-smart-links'                              => [
				'<amp-smartlinks layout="nodisplay" nrtv-account-name="examplepublisher" linkmate exclusive-links link-attribute="href" link-selector="a"></amp-smartlinks>',
				null,
				[ 'amp-smartlinks' ],
			],

			'amp-script-1'                                 => [
				'<amp-script layout="container" src="https://example.com/hello-world.js"><button id="hello">Insert Hello World!</button></amp-script>',
				null,
				[ 'amp-script' ],
			],
			'amp-script-2'                                 => [
				'
					<amp-script layout="container" src="https://example.com/examples/amp-script/hello-world.js">
						<div class="root">
							<button id="hello">Insert Hello World!</button>
							<button id="long">Long task</button>
							<button id="amp-img">Insert amp-img</button>
							<button id="script">Insert &lt;script&gt;</button>
							<button id="img">Insert &lt;img&gt;</button>
						</div>
					</amp-script>
				',
				null,
				[ 'amp-script' ],
			],
			'amp-script-3'                                 => [
				'
					<amp-script src="https://example.com/examples/amp-script/todomvc.ssr.js" layout="container">
						<div><header class="header"><h1>todos</h1><input class="new-todo" placeholder="What needs to be done?" autofocus="true"></header></div>
					</amp-script>
				',
				null,
				[ 'amp-script' ],
			],

			'amp-script-4'                                 => [
				'
					<amp-script layout="container" src="https://example.com/examples/amp-script/empty.js">
						<div class="root">should be empty</div>
					</amp-script>
				',
				null,
				[ 'amp-script' ],
			],

			'amp-script-with-canvas'                       => [
				'
					<amp-script src="https://example.com/examples/amp-script/empty.js">
						<canvas width="100" height="100"></canvas>
					</amp-script>
				',
				null,
				[ 'amp-script' ],
			],

			'amp_img_with_object_fit_position'             => [
				'<amp-img src="http://placehold.it/400x500" width="300" height="300" object-fit="none" object-position="right top" layout="intrinsic"></amp-img>',
				null,
				[],
			],

			'amp_link_rewriter'                            => [
				'<amp-link-rewriter layout="nodisplay"><script type="application/json">{}</script></amp-link-rewriter>',
				null,
				[ 'amp-link-rewriter' ],
			],

			'unique_constraint'                            => [
				str_repeat( '<amp-geo layout="nodisplay"><script type="application/json">{}</script></amp-geo>', 2 ),
				'<amp-geo layout="nodisplay"><script type="application/json">{}</script></amp-geo>',
				[ 'amp-geo' ],
				[ 'duplicate_element' ],
			],

			'amp-autocomplete'                             => [
				'
					<form method="post" action-xhr="/form/echo-json/post" target="_blank" on="submit-success:AMP.setState({result: event.response})">
						<amp-autocomplete id="autocomplete" filter="substring" min-characters="0">
							<input type="text" id="input">
							<script type="application/json" id="script">
							{ "items" : ["apple", "banana", "orange"] }
							</script>
						</amp-autocomplete>
					</form>
					<amp-autocomplete id="autocomplete2" filter="substring" min-characters="0">
						<input type="text" id="input2">
						<script type="application/json" id="script">
						{ "items" : ["red", "green", "blue"] }
						</script>
					</amp-autocomplete>
				',
				null,
				[ 'amp-form', 'amp-autocomplete' ],
			],

			'amp-connatix-player'                          => [
				'<amp-connatix-player data-player-id="03ef71d8-0941-4bff-94f2-74ca3580b497" layout="responsive" width="16" height="9"></amp-connatix-player>',
				null,
				[ 'amp-connatix-player' ],
			],

			'amp-truncate-text'                            => [
				'
					<amp-truncate-text layout="fixed" height="3em" width="20em">
						Some text that may get truncated.
						<button slot="expand">See more</button>
						<button slot="collapse">See less</button>
					</amp-truncate-text>
				',
				null,
				[ 'amp-truncate-text' ],
			],

			'amp-user-location'                            => [
				'
					<button on="tap: location.request()">Use my location</button>
					<amp-user-location id="location" on="approve:AMP.setState({located: true})" layout="nodisplay">
					</amp-user-location>
				',
				null,
				[ 'amp-user-location' ],
			],

			'amp-megaphone'                                => [
				'<amp-megaphone height="166" layout="fixed-height" data-episode="OSC7749686951" data-light></amp-megaphone>',
				null,
				[ 'amp-megaphone' ],
			],

			'amp-minute-media-player'                      => [
				'<amp-minute-media-player dock data-content-type="semantic" data-minimum-date-factor="10" data-scanned-element-type="tag" data-scanned-element="post-body" data-scoped-keywords="football" layout="responsive" width="160" height="96"></amp-minute-media-player>',
				null,
				[ 'amp-minute-media-player', 'amp-video-docking' ],
			],

			'deeply_nested_elements_200'                   => [
				// If a DOM tree is too deep, libxml itself will issue an error: Excessive depth in document: 256 use XML_PARSE_HUGE option.
				// Also, if XDebug is enabled, then max_nesting_level error is reached if call stack is >256. A nesting level of 200 is safe,
				// so this tests up to that level of nesting to also ensure that the recursive \AMP_Tag_And_Attribute_Sanitizer::sanitize_element()
				// method does not cause a different error at the PHP level when a recursion call stack reaches that same level.
				str_repeat( '<div>', 200 ) . '<bad>hello world!</bad>' . str_repeat( '</div>', 200 ),
				str_repeat( '<div>', 200 ) . 'hello world!' . str_repeat( '</div>', 200 ),
				[],
				[ 'invalid_element' ],
			],

			'invalid_php_pi'                               => [
				'<?php $schema = get_post_meta(get_the_ID(), \'schema\', true); if(!empty($schema)) { echo $schema; } ?>',
				'',
				[],
				[ 'invalid_processing_instruction' ],
			],

			'invalid_xml_pi'                               => [
				'<?xml version="1.0" encoding="utf-8"?>',
				'',
				[],
				[ 'invalid_processing_instruction' ],
			],
		];
	}

	/**
	 * Tests is_missing_mandatory_attribute
	 *
	 * @see AMP_Tag_And_Attribute_Sanitizer::is_missing_mandatory_attribute()
	 */
	public function test_is_missing_mandatory_attribute() {
		$spec = [
			'data-gistid' => [
				'mandatory' => true,
			],
			'noloading'   => [],
		];
		$dom  = new DomDocument();
		$node = new DOMElement( 'amp-gist' );
		$dom->appendChild( $node );
		$sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );
		$this->assertTrue( $sanitizer->is_missing_mandatory_attribute( $spec, $node ) );

		$node->setAttribute( 'data-gistid', 'foo-value' );
		$this->assertFalse( $sanitizer->is_missing_mandatory_attribute( $spec, $node ) );
	}

	/**
	 * Test sanitization of tags and attributes.
	 *
	 * @dataProvider get_body_data
	 * @group        allowed-tags
	 *
	 * @param string     $source               Markup to process.
	 * @param string     $expected             The markup to expect.
	 * @param array      $expected_scripts     The AMP component script names that are obtained through sanitization.
	 * @param array|null $expected_error_codes Expected validation error codes.
	 */
	public function test_body_sanitizer( $source, $expected = null, $expected_scripts = [], $expected_error_codes = null ) {
		$expected           = isset( $expected ) ? $expected : $source;
		$dom                = AMP_DOM_Utils::get_dom_from_content( $source );
		$actual_error_codes = [];
		$sanitizer          = new AMP_Tag_And_Attribute_Sanitizer(
			$dom,
			[
				'validation_error_callback' => static function( $error ) use ( &$actual_error_codes ) {
					$actual_error_codes[] = $error['code'];
					return true;
				},
			]
		);
		$sanitizer->sanitize();
		$content = AMP_DOM_Utils::get_content_from_dom( $dom );

		$this->assertEqualMarkup( $expected, $content );
		$this->assertEqualSets( $expected_scripts, array_keys( $sanitizer->get_scripts() ) );
		if ( is_array( $expected_error_codes ) ) {
			$this->assertEqualSets( $expected_error_codes, $actual_error_codes );
		}
	}

	/**
	 * Get data for testing sanitization in the html.
	 *
	 * @return array[] Each array item is a tuple containing pre-sanitized string, sanitized string, and scripts
	 *                 identified during sanitization.
	 */
	public function get_html_data() {
		$data = [
			'meta_charset_and_viewport_and_canonical' => [
				'<html amp lang="ar" dir="rtl"><head><meta charset="utf-8"><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><meta name="viewport" content="width=device-width, minimum-scale=1"><base target="_blank"><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Tangerine"><link rel="canonical" href="self.html"><title>marhabaan bialealim!</title></head><body></body></html>', // phpcs:ignore
			],
			'script_tag_externals'                    => [
				'<html amp><head><meta charset="utf-8"><script async type="text/javascript" src="illegal.js"></script><script async src="illegal.js"></script><script src="illegal.js"></script><script type="text/javascript" src="illegal.js"></script></head><body></body></html>', // phpcs:ignore
				'<html amp><head><meta charset="utf-8"></head><body></body></html>',
			],
			'script_tag_inline'                       => [
				'<html amp><head><meta charset="utf-8"><script type="text/javascript">document.write("bad");</script></head><body></body></html>',
				'<html amp><head><meta charset="utf-8"></head><body></body></html>',
			],
			'style_external'                          => [
				'<html amp><head><meta charset="utf-8"><link rel="stylesheet" href="https://example.com/test.css"></head><body></body></html>', // phpcs:ignore
				'<html amp><head><meta charset="utf-8"></head><body></body></html>',
			],
			'style_inline'                            => [
				'<html amp><head><meta charset="utf-8"><style>body{}</style><style type="text/css">body{}</style></head><body></body></html>',
				'<html amp><head><meta charset="utf-8"></head><body></body></html>',
			],
			'bad_external_font'                       => [
				'<html amp><head><meta charset="utf-8"><link rel="stylesheet" href="https://fonts.example.com/css?family=Bad"></head><body></body></html>', // phpcs:ignore
				'<html amp><head><meta charset="utf-8"></head><body></body></html>',
			],
			'bad_meta_ua_compatible'                  => [
				'<html amp><head><meta charset="utf-8"><meta http-equiv="X-UA-Compatible" content="IE=9,chrome=1"></head><body></body></html>',
				'<html amp><head><meta charset="utf-8"><meta content="IE=9,chrome=1"></head><body></body></html>', // Note the http-equiv is removed because the content violates its attribute spec.
			],
			'bad_meta_charset'                        => [
				'<html amp><head><meta charset="latin-1"><title>Mojibake?</title></head><body></body></html>',
				'<html amp><head><meta><title>Mojibake?</title></head><body></body></html>', // Note the charset attribute is removed because it violates the attribute spec, but the entire element is not removed because charset is not mandatory.
			],
			'bad_meta_viewport'                       => [
				'<html amp><head><meta charset="utf-8"><meta name="viewport" content="maximum-scale=1.0"></head><body></body></html>',
				'<html amp><head><meta charset="utf-8"></head><body></body></html>',
			],
			'edge_meta_ua_compatible'                 => [
				'<html amp><head><meta charset="utf-8"><meta http-equiv="X-UA-Compatible" content="IE=edge"></head><body></body></html>',
				null, // No change.
			],
			'meta_viewport_extras'                    => [
				'<html amp><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,height=device-height,initial-scale=2,maximum-scale=3,minimum-scale=0.5,shrink-to-fit=yes,user-scalable=yes,viewport-fit=cover"></head><body></body></html>',
				null, // No change.
			],
			'meta_og_property'                        => [
				'<html amp><head><meta charset="utf-8"><meta property="og:site_name" content="AMP Site"></head><body></body></html>',
				null, // No change.
			],
			'head_with_valid_amp_illegal_parent'      => [
				'<html amp><head><meta charset="utf-8"><amp-analytics id="75a1fdc3143c" type="googleanalytics"><script type="application/json">{"vars":{"account":"UA-XXXXXX-1"},"triggers":{"trackPageview":{"on":"visible","request":"pageview"}}}</script></amp-analytics></head><body></body></html>',
				'<html amp><head><meta charset="utf-8"></head><body><amp-analytics id="75a1fdc3143c" type="googleanalytics"><script type="application/json">{"vars":{"account":"UA-XXXXXX-1"},"triggers":{"trackPageview":{"on":"visible","request":"pageview"}}}</script></amp-analytics></body></html>',
				[ 'amp-analytics' ],
			],
			'head_with_invalid_nodes'                 => [
				'<html amp><head><meta charset="utf-8"><META NAME="foo" CONTENT="bar"><bad>bad!</bad> other</head><body></body></html>',
				'<html amp><head><meta charset="utf-8"><meta name="foo" content="bar"></head><body>bad!<p> other</p></body></html>',
			],
			'head_with_duplicate_charset'             => [
				'<html amp><head><meta charset="UTF-8"><meta charset="utf-8"><body><p>Content</p></body></html>',
				'<html amp><head><meta charset="UTF-8"></head><body><p>Content</p></body></html>',
				[],
				[ 'duplicate_element' ],
			],
			'head_with_duplicate_viewport'            => [
				'<html amp><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,minimum-scale=1"><meta name="viewport" content="width=device-width"></head><body><p>Content</p></body></html>',
				'<html amp><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,minimum-scale=1"></head><body><p>Content</p></body></html>',
				[],
				[ 'duplicate_element' ],
			],
			'meta_amp_script_src'                     => [
				'<html amp><head><meta charset="utf-8"><meta name="amp-script-src" content="sha384-abc123 sha384-def456"></head><body></body></html>',
				null, // No change.
			],
			'link_without_valid_mandatory_href'       => [
				'<html amp><head><meta charset="utf-8"><link rel="manifest" href="https://bad@"></head><body></body></html>',
				'<html amp><head><meta charset="utf-8"></head><body></body></html>',
			],
		];

		$bad_dev_mode_document = sprintf(
			'
				<html amp data-ampdevmode>
					<head>
						<meta charset="utf-8">
						<style data-ampdevmode>%s</style>
					</head>
					<body>
						<amp-state id="something">
							<script type="application/json" data-ampdevmode>%s</script>
						</amp-state>
						<button data-ampdevmode onclick="alert(\'Hello!\')"></button>
						<script data-ampdevmode>document.write("Hello World!")</script>
					</body>
				</html>
				',
			'button::before { content:"' . str_repeat( 'a', 50001 ) . '";"}',
			'{"foo":"' . str_repeat( 'b', 100001 ) . '"}'
		);

		$data['dev_mode_test'] = [
			$bad_dev_mode_document,
			null,
			[ 'amp-bind' ],
			[], // All validation errors suppressed.
		];

		$data['bad_document_without_dev_mode'] = [
			str_replace( 'data-ampdevmode', '', $bad_dev_mode_document ),
			'
			<html amp>
				<head>
					<meta charset="utf-8">
				</head>
				<body>
					<amp-state id="something"></amp-state>
					<button></button>
				</body>
			</html>
			',
			[ 'amp-bind' ],
			[
				'invalid_attribute',
				'invalid_element',
				'invalid_element',
				'invalid_element',
			],
		];

		$max_size = null;
		foreach ( AMP_Allowed_Tags_Generated::get_allowed_tag( 'style' ) as $spec_rule ) {
			if ( isset( $spec_rule[ AMP_Rule_Spec::TAG_SPEC ]['spec_name'] ) && 'style amp-custom' === $spec_rule[ AMP_Rule_Spec::TAG_SPEC ]['spec_name'] ) {
				$max_size = $spec_rule[ AMP_Rule_Spec::CDATA ]['max_bytes'];
				break;
			}
		}
		if ( ! $max_size ) {
			throw new Exception( 'Could not find amp-custom max_bytes' );
		}

		$dont_strip_excessive_css_in_amp_custom_document = sprintf(
			'
				<html amp>
					<head>
						<meta charset="utf-8">
						<style amp-custom>%s</style>
					</head>
					<body>
					</body>
				</html>
				',
			'body::after { content:"' . str_repeat( 'a', $max_size ) . '";"}'
		);

		$data['dont_strip_excessive_css_in_amp_custom_document'] = [
			$dont_strip_excessive_css_in_amp_custom_document,
		];

		// Also include the body tests.
		$html_doc_format = '<html amp><head><meta charset="utf-8"></head><body><!-- before -->%s<!-- after --></body></html>';
		foreach ( $this->get_body_data() as $body_test ) {
			$html_test = [
				sprintf( $html_doc_format, array_shift( $body_test ) ),
			];
			$expected  = array_shift( $body_test );
			if ( isset( $expected ) ) {
				$expected = sprintf( $html_doc_format, $expected );
			}
			$html_test[] = $expected;
			$html_test[] = array_shift( $body_test );
			$html_test[] = array_shift( $body_test );
			$data[]      = $html_test;
		}

		return $data;
	}

	/**
	 * Test sanitization of tags and attributes for the entire document, including the HEAD.
	 *
	 * @dataProvider get_html_data
	 * @group        allowed-tags
	 *
	 * @param string     $source               Markup to process.
	 * @param string     $expected             The markup to expect.
	 * @param array      $expected_scripts     The AMP component script names that are obtained through sanitization.
	 * @param array|null $expected_error_codes Expected validation error codes.
	 */
	public function test_html_sanitizer( $source, $expected = null, $expected_scripts = [], $expected_error_codes = null ) {
		$expected           = isset( $expected ) ? $expected : $source;
		$dom                = AMP_DOM_Utils::get_dom( $source );
		$actual_error_codes = [];
		$sanitizer          = new AMP_Tag_And_Attribute_Sanitizer(
			$dom,
			[
				'use_document_element'      => true,
				'validation_error_callback' => static function( $error ) use ( &$actual_error_codes ) {
					$actual_error_codes[] = $error['code'];
					return true;
				},
			]
		);
		$sanitizer->sanitize();
		$content = AMP_DOM_Utils::get_content_from_dom_node( $dom, $dom->documentElement );

		if ( is_array( $expected_error_codes ) ) {
			$this->assertEqualSets( $expected_error_codes, $actual_error_codes );
		}

		$this->assertEqualMarkup( $expected, $content );

		if ( is_array( $expected_scripts ) ) {
			$this->assertEqualSets( $expected_scripts, array_keys( $sanitizer->get_scripts() ) );
		}
	}

	/**
	 * Get data for replace_node_with_children_validation_errors test.
	 *
	 * @return array[] Test data.
	 */
	public function get_data_for_replace_node_with_children_validation_errors() {
		return [
			'amp_image'                => [
				'<amp-image src="/none.jpg" width="100" height="100" alt="None"></amp-image>',
				'',
				[
					[
						'node_name'       => 'amp-image',
						'parent_name'     => 'body',
						'code'            => 'invalid_element',
						'node_attributes' => [
							'src'    => '/none.jpg',
							'width'  => '100',
							'height' => '100',
							'alt'    => 'None',
						],
						'type'            => AMP_Validation_Error_Taxonomy::HTML_ELEMENT_ERROR_TYPE,
					],
				],
			],

			'invalid_parent_element'   => [
				'<baz class="baz-invalid"><p>Invalid baz parent element.</p></baz>',
				'<p>Invalid baz parent element.</p>',
				[
					[
						'node_name'       => 'baz',
						'parent_name'     => 'body',
						'code'            => 'invalid_element',
						'node_attributes' => [ 'class' => 'baz-invalid' ],
						'type'            => AMP_Validation_Error_Taxonomy::HTML_ELEMENT_ERROR_TYPE,
					],
				],
			],

			'invalid_a_tag'            => [
				'<amp-story-grid-layer class="a-invalid"><a>Invalid a tag.</a></amp-story-grid-layer>',
				'',
				[
					[
						'node_name'       => 'amp-story-grid-layer',
						'parent_name'     => 'body',
						'code'            => 'invalid_element',
						'node_attributes' => [ 'class' => 'a-invalid' ],
						'type'            => AMP_Validation_Error_Taxonomy::HTML_ELEMENT_ERROR_TYPE,
					],
				],
			],

			'invalid_foo_tag'          => [
				'<foo class="foo-invalid">Invalid foo tag.</foo>',
				'Invalid foo tag.',
				[
					[
						'node_name'       => 'foo',
						'parent_name'     => 'body',
						'code'            => 'invalid_element',
						'node_attributes' => [ 'class' => 'foo-invalid' ],
						'type'            => AMP_Validation_Error_Taxonomy::HTML_ELEMENT_ERROR_TYPE,
					],
				],
			],

			'invalid_barbaz_tag'       => [
				'<bazbar><span>Is an invalid "bazbar" tag.</span></bazbar>',
				'<span>Is an invalid "bazbar" tag.</span>',
				[
					[
						'node_name'       => 'bazbar',
						'parent_name'     => 'body',
						'code'            => 'invalid_element',
						'node_attributes' => [],
						'type'            => AMP_Validation_Error_Taxonomy::HTML_ELEMENT_ERROR_TYPE,
					],
				],
			],

			'nested_valid_and_invalud' => [
				'
					<div class="parent">
						<p>Nesting valid and invalid elements.</p>
						<invalid_p id="invalid">Is an invalid "invalid" tag</invalid_p>
						<bazfoo>Is an invalid "foo" tag <p>This should pass.</p></bazfoo>
					</div>
				',
				'
					<div class="parent">
						<p>Nesting valid and invalid elements.</p>
						Is an invalid "invalid" tagIs an invalid "foo" tag <p>This should pass.</p>
					</div>
				',
				[
					[
						'node_name'       => 'invalid_p',
						'parent_name'     => 'div',
						'code'            => 'invalid_element',
						'node_attributes' => [ 'id' => 'invalid' ],
						'type'            => AMP_Validation_Error_Taxonomy::HTML_ELEMENT_ERROR_TYPE,
					],
					[
						'node_name'       => 'bazfoo',
						'parent_name'     => 'div',
						'code'            => 'invalid_element',
						'node_attributes' => [],
						'type'            => AMP_Validation_Error_Taxonomy::HTML_ELEMENT_ERROR_TYPE,
					],
				],
			],

			'bad_lili'                 => [
				'
					<ul>
						<li>hello</li>
						<lili>world</lili>
					</ul>
				',
				'<ul><li>hello</li> world</ul>',
				[
					[
						'node_name'       => 'lili',
						'parent_name'     => 'ul',
						'code'            => 'invalid_element',
						'node_attributes' => [],
						'type'            => AMP_Validation_Error_Taxonomy::HTML_ELEMENT_ERROR_TYPE,
					],
				],
			],

			'invalid_nested_elements'  => [
				'<divs><foo>Invalid <span>nested elements</span></foo></divs>',
				'Invalid <span>nested elements</span>',
				[
					[
						'node_name'       => 'foo',
						'parent_name'     => 'divs',
						'code'            => 'invalid_element',
						'node_attributes' => [],
						'type'            => AMP_Validation_Error_Taxonomy::HTML_ELEMENT_ERROR_TYPE,
					],
					[
						'node_name'       => 'divs',
						'parent_name'     => 'body',
						'code'            => 'invalid_element',
						'node_attributes' => [],
						'type'            => AMP_Validation_Error_Taxonomy::HTML_ELEMENT_ERROR_TYPE,
					],
				],
			],
		];
	}

	/**
	 * Tests replace_node_with_children validation errors.
	 *
	 * @dataProvider get_data_for_replace_node_with_children_validation_errors
	 * @covers \AMP_Tag_And_Attribute_Sanitizer::replace_node_with_children()
	 *
	 * @param string  $source_content   Source DOM content.
	 * @param string  $expected_content Expected content after sanitization.
	 * @param array[] $expected_errors  Expected errors.
	 */
	public function test_replace_node_with_children_validation_errors( $source_content, $expected_content, $expected_errors ) {
		$dom       = AMP_DOM_Utils::get_dom_from_content( $source_content );
		$sanitizer = new AMP_Tag_And_Attribute_Sanitizer(
			$dom,
			[
				'validation_error_callback' => function( $error, $context ) use ( &$expected_errors ) {
					$expected = array_shift( $expected_errors );
					$tag      = $expected['node_name'];
					$this->assertEquals( $expected, $error );
					$this->assertInstanceOf( 'DOMElement', $context['node'] );
					$this->assertEquals( $tag, $context['node']->tagName );
					$this->assertEquals( $tag, $context['node']->nodeName );
					return true;
				},
			]
		);
		$sanitizer->sanitize();

		$this->assertEmpty( $expected_errors, 'There should be no expected validation errors remaining.' );
		$this->assertEqualMarkup( $expected_content, AMP_DOM_Utils::get_content_from_dom( $dom ) );
	}

	/**
	 * Assert markup is equal.
	 *
	 * @param string $expected Expected markup.
	 * @param string $actual   Actual markup.
	 */
	public function assertEqualMarkup( $expected, $actual ) {
		$actual   = preg_replace( '/\s+/', ' ', $actual );
		$expected = preg_replace( '/\s+/', ' ', $expected );
		$actual   = preg_replace( '/(?<=>)\s+(?=<)/', '', trim( $actual ) );
		$expected = preg_replace( '/(?<=>)\s+(?=<)/', '', trim( $expected ) );

		$this->assertEquals(
			array_filter( preg_split( '#(<[^>]+>|[^<>]+)#', $expected, -1, PREG_SPLIT_DELIM_CAPTURE ) ),
			array_filter( preg_split( '#(<[^>]+>|[^<>]+)#', $actual, -1, PREG_SPLIT_DELIM_CAPTURE ) )
		);
	}
}
