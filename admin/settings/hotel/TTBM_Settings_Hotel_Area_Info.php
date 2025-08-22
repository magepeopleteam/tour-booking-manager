<?php
	/**
	 * @author Sahahdat Hossain <raselsha@gmail.com>
	 * @license mage-people.com
	 * @var 1.0.0
	 */
	if (!defined('ABSPATH'))
		die;
	if (!class_exists('TTBM_hotel_area_info')) {
		class TTBM_hotel_area_info {
			public function __construct() {
				add_action('add_ttbm_settings_hotel_tab_content', [$this, 'naearest_area_settings']);

				//add_action('ttbm_single_faq', [$this, 'show_faq_frontend']);

				add_action('admin_enqueue_scripts', [$this, 'my_custom_editor_enqueue']);
				// save faq data
				add_action('wp_ajax_ttbm_hotel_faq_save', [$this, 'save_faq_data_settings']);
				// update faq data
				add_action('wp_ajax_ttbm_hotel_faq_update', [$this, 'faq_data_update']);
				// ttbm_delete_faq_data
				add_action('wp_ajax_ttbm_hotel_faq_delete', [$this, 'faq_delete_item']);
				// FAQ sort_faq
				add_action('wp_ajax_ttbm_hotel_ttbm_faq_sort', [$this, 'sort_faq']);
			}

			public function my_custom_editor_enqueue() {
				// Enqueue necessary scripts
				wp_enqueue_script('jquery');
				wp_enqueue_script('editor');
				wp_enqueue_script('media-upload');
				wp_enqueue_script('thickbox');
				wp_enqueue_style('thickbox');
			}

			public function naearest_area_settings($post_id) {
				$faq_status = get_post_meta($post_id, 'ttbm_hotel_area_status', 'off');
				$active_class = $faq_status == 'on' ? 'mActive' : '';
				$ttbm_faq_active_checked = $faq_status == 'on' ? 'checked' : '';
				?>
                <div class="tabsItem ttbm_settings_hotel_area_info" data-tabs="#ttbm_settings_hotel_area_info">
                    
                    <h2><?php esc_html_e('Hotel area info', 'tour-booking-manager'); ?></h2>
                    <p><?php esc_html_e('Hotel area info Settings will be here.', 'tour-booking-manager'); ?></p>
                    <style>


						.category-section {
							background: white;
							border-radius: 12px;
							padding: 24px;
							margin-bottom: 20px;
							box-shadow: 0 2px 8px rgba(0,0,0,0.1);
						}

						.category-header {
							display: flex;
							gap: 16px;
							margin-bottom: 20px;
						}

						.category-title-input {
							flex: 1;
							padding: 12px 16px;
							border: 2px solid #e1e5e9;
							border-radius: 8px;
							font-size: 14px;
							font-weight: 600;
							background-color: #f8f9fa;
						}

						.category-label-input {
							flex: 1;
							padding: 12px 16px;
							border: 2px solid #e1e5e9;
							border-radius: 8px;
							font-size: 14px;
							color: #6c757d;
						}

						.feature-item {
							display: flex;
							align-items: center;
							gap: 12px;
							margin-bottom: 12px;
						}

						.feature-icon {
							width: 40px;
							height: 40px;
							background: linear-gradient(135deg, #e91e63, #ad1457);
							border-radius: 8px;
							display: flex;
							align-items: center;
							justify-content: center;
							color: white;
							font-size: 18px;
							flex-shrink: 0;
						}

						.feature-input {
							flex: 1;
							padding: 12px 16px;
							border: 2px solid #e1e5e9;
							border-radius: 8px;
							font-size: 14px;
						}

						.action-buttons {
							display: flex;
							gap: 8px;
						}

						.btn {
							border: none;
							border-radius: 8px;
							cursor: pointer;
							display: flex;
							align-items: center;
							justify-content: center;
							font-size: 14px;
							font-weight: 500;
							transition: all 0.2s;
						}

						.btn-icon {
							width: 36px;
							height: 36px;
						}

						.btn-add {
							background: linear-gradient(135deg, #e91e63, #ad1457);
							color: white;
						}

						.btn-delete {
							background: linear-gradient(135deg, #e91e63, #ad1457);
							color: white;
						}

						.btn-add-feature {
							background: linear-gradient(135deg, #17a2b8, #138496);
							color: white;
							padding: 12px 20px;
							margin-top: 16px;
						}

						.btn-add-category {
							background: linear-gradient(135deg, #e91e63, #ad1457);
							color: white;
							padding: 16px 32px;
							font-size: 16px;
							margin: 20px auto;
							display: block;
						}

						.btn:hover {
							transform: translateY(-1px);
							box-shadow: 0 4px 12px rgba(0,0,0,0.15);
						}

						.feature-icons {
							display: grid;
							grid-template-columns: repeat(auto-fit, minmax(40px, 1fr));
							gap: 8px;
							max-width: 200px;
						}

						.floating-actions {
							position: fixed;
							right: 24px;
							top: 50%;
							transform: translateY(-50%);
							display: flex;
							flex-direction: column;
							gap: 8px;
						}

						/* Icon styles */
						.icon-brake::before { content: "🔧"; }
						.icon-shock::before { content: "🔩"; }
						.icon-light::before { content: "💡"; }
						.icon-generic::before { content: "⚙️"; }
						.icon-plus::before { content: "+"; }
						.icon-trash::before { content: "🗑️"; }
					</style>
                    <section>
                        <div class="ttbm-header">
                            <h4><i class="fas fa-question-circle"></i><?php esc_html_e('Enable FAQ Section', 'tour-booking-manager'); ?></h4>
                            <?php TTBM_Custom_Layout::switch_button('ttbm_hotel_faq_status', $ttbm_faq_active_checked); ?>
                        </div>
						<?php 
							$this->show_area_category($post_id);
						?>
						<div class="container">
							<div id="categories-container"></div>

							<button class="btn btn-add-category" onclick="addCategory()">
								<span class="icon-plus"></span>
								Add New Feature Category
							</button>
						</div>
						<script>
							let categoryData = [
								{
									id: 1,
									title: "Feature Category Title",
									label: "Bike Features",
									features: [
										{ id: 1, name: "Disc Brakes", icon: "brake" },
										{ id: 2, name: "Shock Absorbers", icon: "shock" },
										{ id: 3, name: "Headlight and Taillight", icon: "light" }
									]
								},
								{
									id: 2,
									title: "Feature Category Title",
									label: "Feature Category Label",
									features: [
										{ id: 4, name: "Features Name", icon: "generic" }
									]
								}
							];

							let nextCategoryId = 3;
							let nextFeatureId = 5;

							function renderCategories() {
								const container = document.getElementById('categories-container');
								container.innerHTML = '';

								categoryData.forEach(category => {
									const categoryDiv = document.createElement('div');
									categoryDiv.className = 'category-section';
									categoryDiv.innerHTML = `
										<div class="category-header">
											<input type="text" class="category-title-input" 
												value="${category.title}" 
												onchange="updateCategoryTitle(${category.id}, this.value)"
												placeholder="Feature Category Title">
											<input type="text" class="category-label-input" 
												value="${category.label}" 
												onchange="updateCategoryLabel(${category.id}, this.value)"
												placeholder="Feature Category Label">
											<div class="feature-icons">
												<button class="btn btn-icon btn-add" onclick="addFeature(${category.id})">
													<span class="icon-plus"></span>
												</button>
												<button class="btn btn-icon btn-delete" onclick="deleteCategory(${category.id})">
													<span class="icon-trash"></span>
												</button>
											</div>
										</div>
										<div class="features-list">
											${category.features.map(feature => `
												<div class="feature-item">
													<div class="feature-icon icon-${feature.icon}"></div>
													<input type="text" class="feature-input" 
														value="${feature.name}" 
														onchange="updateFeatureName(${category.id}, ${feature.id}, this.value)"
														placeholder="Feature Name">
													<div class="action-buttons">
														<button class="btn btn-icon btn-add" onclick="addFeature(${category.id})">
															<span class="icon-plus"></span>
														</button>
														<button class="btn btn-icon btn-delete" onclick="deleteFeature(${category.id}, ${feature.id})">
															<span class="icon-trash"></span>
														</button>
													</div>
												</div>
											`).join('')}
										</div>
										<button class="btn btn-add-feature" onclick="addFeature(${category.id})">
											<span class="icon-plus"></span>
											Add New Feature
										</button>
									`;
									container.appendChild(categoryDiv);
								});
							}

							function addCategory() {
								const newCategory = {
									id: nextCategoryId++,
									title: "Feature Category Title",
									label: "Feature Category Label",
									features: []
								};
								categoryData.push(newCategory);
								renderCategories();
							}

							function deleteCategory(categoryId) {
								categoryData = categoryData.filter(cat => cat.id !== categoryId);
								renderCategories();
							}

							function updateCategoryTitle(categoryId, newTitle) {
								const category = categoryData.find(cat => cat.id === categoryId);
								if (category) {
									category.title = newTitle;
								}
							}

							function updateCategoryLabel(categoryId, newLabel) {
								const category = categoryData.find(cat => cat.id === categoryId);
								if (category) {
									category.label = newLabel;
								}
							}

							function addFeature(categoryId) {
								const category = categoryData.find(cat => cat.id === categoryId);
								if (category) {
									const newFeature = {
										id: nextFeatureId++,
										name: "New Feature",
										icon: "generic"
									};
									category.features.push(newFeature);
									renderCategories();
								}
							}

							function deleteFeature(categoryId, featureId) {
								const category = categoryData.find(cat => cat.id === categoryId);
								if (category) {
									category.features = category.features.filter(feature => feature.id !== featureId);
									renderCategories();
								}
							}

							function updateFeatureName(categoryId, featureId, newName) {
								const category = categoryData.find(cat => cat.id === categoryId);
								if (category) {
									const feature = category.features.find(f => f.id === featureId);
									if (feature) {
										feature.name = newName;
									}
								}
							}

							// Initialize the app
							renderCategories();
						</script>
                    </section>
                </div>
				<?php
			}

			public function show_area_category($post_id) {
				$ttbm_hotel_area_info = get_post_meta($post_id,'ttbm_hotel_area_info',true);
				$ttbm_hotel_area_info = !empty($ttbm_hotel_area_info)? $ttbm_hotel_area_info :[];
				$ttbm_hotel_area_info =[
					[
						'cat_icon'=>'mi mi-home',
						'cat_title'=>'What\'s nearby',
						'cat_sub'=>[
							[
								'cat_icon'=>'mi mi-home',
								'cat_title'=>'Národný park Nízke Tatry',
								'cat_distance'=>12,
								'cat_dist_type'=>'km',
							],
							[
								'cat_icon'=>'mi mi-home',
								'cat_title'=>'Národný park Nízke Tatry',
								'cat_distance'=>12,
								'cat_dist_type'=>'km',

							],
						],
						
					],
					[
						'cat_icon'=>'mi mi-restaurants',
						'cat_title'=>'Restaurants & cafes',
						'cat_sub'=>[
							[
								'cat_icon'=>'mi mi-home',
								'cat_title'=>'Národný park Nízke Tatry',
								'cat_distance'=>12,
								'cat_dist_type'=>'km',
							],
							[
								'cat_icon'=>'mi mi-home',
								'cat_title'=>'Národný park Nízke Tatry',
								'cat_distance'=>12,
								'cat_dist_type'=>'km',

							],
						],
						
					],
					[
						'cat_icon'=>'mi mi-spa',
						'cat_title'=>'Natural Beauty',
						'cat_sub'=>[
							[
								'cat_icon'=>'mi mi-home',
								'cat_title'=>'Národný park Nízke Tatry',
								'cat_distance'=>12,
								'cat_dist_type'=>'km',
							],
							[
								'cat_icon'=>'mi mi-home',
								'cat_title'=>'Národný park Nízke Tatry',
								'cat_distance'=>12,
								'cat_dist_type'=>'km',

							],
						],
						
					]
				];
				?>
				<div class="category-section">
					<?php foreach($ttbm_hotel_area_info as $value):?>
						<div class="category-header">
							<input type="text" class="category-title-input" 
								value="" 
								onchange="updateCategoryTitle()"
								placeholder="Feature Category Title">
							<input type="text" class="category-label-input" 
								value="" 
								onchange=""
								placeholder="Feature Category Label">
							<div class="feature-icons">
								<button class="btn btn-icon btn-add" onclick="">
									<span class="icon-plus"></span>
								</button>
								<button class="btn btn-icon btn-delete" onclick="deleteCategory()">
									<span class="icon-trash"></span>
								</button>
							</div>
						</div>
						<div class="features-list">
							<?php foreach($value['cat_sub'] as $value):?>
								<div class="feature-item">
									<div class="feature-icon icon-${feature.icon}"></div>
									<input type="text" class="feature-input" 
										value="${feature.name}" 
										onchange="updateFeatureName()"
										placeholder="Feature Name">
									<div class="action-buttons">
										<button class="btn btn-icon btn-add" onclick="addFeature()">
											<span class="icon-plus"></span>
										</button>
										<button class="btn btn-icon btn-delete" onclick="deleteFeature()">
											<span class="icon-trash"></span>
										</button>
									</div>
								</div>
							<?php  endforeach; ?>
						</div>
					<?php  endforeach; ?>
				</div>
				<button class="btn btn-add-feature" onclick="addFeature()">
					<span class="icon-plus"></span>
					Add New Feature
				</button>
				<?php
			}

		}
		new TTBM_hotel_area_info();
	}