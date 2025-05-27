<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\Content;
use App\Models\User;
use Illuminate\Support\Str;

class MenuContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first admin user to assign as content creator
        $adminUser = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['super_admin', 'admin']);
        })->first();

        if (!$adminUser) {
            $this->command->warn('No admin user found. Creating content without user assignment.');
        }

        // Create content items first
        $this->createContentItems($adminUser);
        
        // Create menu structure
        $this->createMenuStructure();

        $this->command->info('Menu and Content seeded successfully!');
    }

    /**
     * Create sample content items
     */
    private function createContentItems($adminUser)
    {
        $contents = [
            // Dashboard Content
            [
                'title' => 'Analytics Dashboard Overview',
                'slug' => 'analytics-dashboard-overview',
                'type' => 'custom',
                'custom_content' => $this->getDashboardContent(),
                'created_by_user_id' => $adminUser?->id,
                'updated_by_user_id' => $adminUser?->id,
            ],

            // Company Information
            [
                'title' => 'About Indonet',
                'slug' => 'about-indonet',
                'type' => 'custom',
                'custom_content' => $this->getAboutContent(),
                'created_by_user_id' => $adminUser?->id,
                'updated_by_user_id' => $adminUser?->id,
            ],

            // Data Analytics Content
            [
                'title' => 'Sales Performance Analytics',
                'slug' => 'sales-performance-analytics',
                'type' => 'custom',
                'custom_content' => $this->getSalesAnalyticsContent(),
                'created_by_user_id' => $adminUser?->id,
                'updated_by_user_id' => $adminUser?->id,
            ],

            [
                'title' => 'Customer Analytics Dashboard',
                'slug' => 'customer-analytics-dashboard',
                'type' => 'custom',
                'custom_content' => $this->getCustomerAnalyticsContent(),
                'created_by_user_id' => $adminUser?->id,
                'updated_by_user_id' => $adminUser?->id,
            ],

            [
                'title' => 'Financial Reports Hub',
                'slug' => 'financial-reports-hub',
                'type' => 'custom',
                'custom_content' => $this->getFinancialReportsContent(),
                'created_by_user_id' => $adminUser?->id,
                'updated_by_user_id' => $adminUser?->id,
            ],

            // Reports Content
            [
                'title' => 'Monthly Performance Report',
                'slug' => 'monthly-performance-report',
                'type' => 'custom',
                'custom_content' => $this->getMonthlyReportContent(),
                'created_by_user_id' => $adminUser?->id,
                'updated_by_user_id' => $adminUser?->id,
            ],

            [
                'title' => 'Quarterly Business Review',
                'slug' => 'quarterly-business-review',
                'type' => 'custom',
                'custom_content' => $this->getQuarterlyReportContent(),
                'created_by_user_id' => $adminUser?->id,
                'updated_by_user_id' => $adminUser?->id,
            ],

            // Training & Resources
            [
                'title' => 'Analytics Training Materials',
                'slug' => 'analytics-training-materials',
                'type' => 'custom',
                'custom_content' => $this->getTrainingContent(),
                'created_by_user_id' => $adminUser?->id,
                'updated_by_user_id' => $adminUser?->id,
            ],

            [
                'title' => 'User Guide & Documentation',
                'slug' => 'user-guide-documentation',
                'type' => 'custom',
                'custom_content' => $this->getUserGuideContent(),
                'created_by_user_id' => $adminUser?->id,
                'updated_by_user_id' => $adminUser?->id,
            ],

            // Tools & Utilities
            [
                'title' => 'Data Export Tools',
                'slug' => 'data-export-tools',
                'type' => 'custom',
                'custom_content' => $this->getDataExportContent(),
                'created_by_user_id' => $adminUser?->id,
                'updated_by_user_id' => $adminUser?->id,
            ],

            [
                'title' => 'Report Generator',
                'slug' => 'report-generator',
                'type' => 'custom',
                'custom_content' => $this->getReportGeneratorContent(),
                'created_by_user_id' => $adminUser?->id,
                'updated_by_user_id' => $adminUser?->id,
            ],

            // External Embedded Content
            [
                'title' => 'Power BI Analytics Dashboard',
                'slug' => 'powerbi-analytics-dashboard',
                'type' => 'embed_url',
                'embed_url_original' => 'https://app.fabric.microsoft.com/view?r=eyJrIjoiMWRkYTc5YzgtYzk5OS00OWU3LWFhZWItMjQ3Y2M3MTVhYWI2IiwidCI6ImY0ZDkxZGM1LWY0MzQtNDBkOC1iZjRhLWYwNTEzZTc0ZWE1NSIsImMiOjN9',
                'created_by_user_id' => $adminUser?->id,
                'updated_by_user_id' => $adminUser?->id,
            ],

            [
                'title' => 'Power BI Public Visualization',
                'slug' => 'powerbi-public-visualization',
                'type' => 'embed_url',
                'embed_url_original' => 'https://app.fabric.microsoft.com/view?r=eyJrIjoiMjA5ZTFlMzgtOTI1NC00NmViLWFlMzgtOTgwODY2MTU2MGQ1IiwidCI6IjExMmM5ZWQ0LTYzNTctNDViYi1iYmU5LTAxOGZkYjM1ZThlZSIsImMiOjEwfQ%3D%3D',
                'created_by_user_id' => $adminUser?->id,
                'updated_by_user_id' => $adminUser?->id,
            ],

            // System Information
            [
                'title' => 'System Status & Health',
                'slug' => 'system-status-health',
                'type' => 'custom',
                'custom_content' => $this->getSystemStatusContent(),
                'created_by_user_id' => $adminUser?->id,
                'updated_by_user_id' => $adminUser?->id,
            ],

            [
                'title' => 'API Documentation',
                'slug' => 'api-documentation',
                'type' => 'custom',
                'custom_content' => $this->getApiDocumentationContent(),
                'created_by_user_id' => $adminUser?->id,
                'updated_by_user_id' => $adminUser?->id,
            ],

            // Help & Support
            [
                'title' => 'Contact Support',
                'slug' => 'contact-support',
                'type' => 'custom',
                'custom_content' => $this->getContactSupportContent(),
                'created_by_user_id' => $adminUser?->id,
                'updated_by_user_id' => $adminUser?->id,
            ],

            [
                'title' => 'FAQ & Troubleshooting',
                'slug' => 'faq-troubleshooting',
                'type' => 'custom',
                'custom_content' => $this->getFaqContent(),
                'created_by_user_id' => $adminUser?->id,
                'updated_by_user_id' => $adminUser?->id,
            ],
        ];

        foreach ($contents as $contentData) {
            Content::firstOrCreate(
                ['slug' => $contentData['slug']],
                $contentData
            );
        }

        $this->command->info('Content items created successfully!');
    }

    /**
     * Create hierarchical menu structure
     */
    private function createMenuStructure()
    {
        // Create root menus with different permission requirements
        $dashboardMenu = Menu::updateOrCreate(
            [
                'name' => 'Dashboard',
                'type' => 'content_menu',
                'parent_id' => null,
                'order' => 1,
            ],
            [
                'icon' => 'fas fa-tachometer-alt',
                'route_or_url' => '/dashboard',
                'content_id' => Content::where('slug', 'analytics-dashboard-overview')->first()?->id,
                'role_permissions_required' => ['analytics.view'],
            ]
        );

        $analyticsMenu = Menu::updateOrCreate(
            [
                'name' => 'Analytics',
                'type' => 'list_menu',
                'parent_id' => null,
                'order' => 2,
            ],
            [
                'icon' => 'fas fa-chart-line',
                'route_or_url' => null,
                'role_permissions_required' => ['analytics.view'],
            ]
        );

        $reportsMenu = Menu::updateOrCreate(
            [
                'name' => 'Reports',
                'type' => 'list_menu',
                'parent_id' => null,
                'order' => 3,
            ],
            [
                'icon' => 'fas fa-file-alt',
                'route_or_url' => null,
                'role_permissions_required' => ['analytics.view'],
            ]
        );

        $toolsMenu = Menu::updateOrCreate(
            [
                'name' => 'Tools',
                'type' => 'list_menu',
                'parent_id' => null,
                'order' => 4,
            ],
            [
                'icon' => 'fas fa-tools',
                'route_or_url' => null,
                'role_permissions_required' => ['data.view'],
            ]
        );

        $resourcesMenu = Menu::updateOrCreate(
            [
                'name' => 'Resources',
                'type' => 'list_menu',
                'parent_id' => null,
                'order' => 5,
            ],
            [
                'icon' => 'fas fa-book',
                'route_or_url' => null,
                'role_permissions_required' => null, // Available to all authenticated users
            ]
        );

        $adminMenu = Menu::updateOrCreate(
            [
                'name' => 'Administration',
                'type' => 'list_menu',
                'parent_id' => null,
                'order' => 6,
            ],
            [
                'icon' => 'fas fa-cogs',
                'route_or_url' => null,
                'role_permissions_required' => ['admin.view'],
            ]
        );

        $supportMenu = Menu::updateOrCreate(
            [
                'name' => 'Support',
                'type' => 'list_menu',
                'parent_id' => null,
                'order' => 7,
            ],
            [
                'icon' => 'fas fa-life-ring',
                'route_or_url' => null,
                'role_permissions_required' => null, // Available to all authenticated users
            ]
        );

        // Create Analytics submenu items
        $this->createSubmenuItems($analyticsMenu->id, [
            [
                'name' => 'Sales Performance',
                'type' => 'content_menu',
                'icon' => 'fas fa-chart-bar',
                'content_slug' => 'sales-performance-analytics',
                'order' => 1,
                'permissions' => ['analytics.view'],
            ],
            [
                'name' => 'Customer Analytics',
                'type' => 'content_menu',
                'icon' => 'fas fa-users',
                'content_slug' => 'customer-analytics-dashboard',
                'order' => 2,
                'permissions' => ['analytics.view'],
            ],
            [
                'name' => 'Financial Reports',
                'type' => 'content_menu',
                'icon' => 'fas fa-dollar-sign',
                'content_slug' => 'financial-reports-hub',
                'order' => 3,
                'permissions' => ['analytics.view'],
            ],
            [
                'name' => 'External Analytics',
                'type' => 'list_menu',
                'icon' => 'fas fa-external-link-alt',
                'order' => 4,
                'permissions' => ['analytics.view'],
                'children' => [
                    [
                        'name' => 'Google Analytics',
                        'type' => 'content_menu',
                        'icon' => 'fab fa-google',
                        'content_slug' => 'google-analytics-dashboard',
                        'order' => 1,
                        'permissions' => ['analytics.view'],
                    ],
                    [
                        'name' => 'Tableau Dashboard',
                        'type' => 'content_menu',
                        'icon' => 'fas fa-chart-pie',
                        'content_slug' => 'tableau-public-visualization',
                        'order' => 2,
                        'permissions' => ['analytics.view'],
                    ],
                ]
            ],
        ]);

        // Create Reports submenu items
        $this->createSubmenuItems($reportsMenu->id, [
            [
                'name' => 'Monthly Reports',
                'type' => 'content_menu',
                'icon' => 'fas fa-calendar-alt',
                'content_slug' => 'monthly-performance-report',
                'order' => 1,
                'permissions' => ['analytics.view'],
            ],
            [
                'name' => 'Quarterly Reports',
                'type' => 'content_menu',
                'icon' => 'fas fa-calendar-check',
                'content_slug' => 'quarterly-business-review',
                'order' => 2,
                'permissions' => ['analytics.view'],
            ],
            [
                'name' => 'Custom Reports',
                'type' => 'content_menu',
                'icon' => 'fas fa-file-invoice',
                'content_slug' => 'report-generator',
                'order' => 3,
                'permissions' => ['analytics.create'],
            ],
        ]);

        // Create Tools submenu items
        $this->createSubmenuItems($toolsMenu->id, [
            [
                'name' => 'Data Export',
                'type' => 'content_menu',
                'icon' => 'fas fa-download',
                'content_slug' => 'data-export-tools',
                'order' => 1,
                'permissions' => ['data.export'],
            ],
            [
                'name' => 'Report Builder',
                'type' => 'content_menu',
                'icon' => 'fas fa-hammer',
                'content_slug' => 'report-generator',
                'order' => 2,
                'permissions' => ['analytics.create'],
            ],
        ]);

        // Create Resources submenu items
        $this->createSubmenuItems($resourcesMenu->id, [
            [
                'name' => 'About Company',
                'type' => 'content_menu',
                'icon' => 'fas fa-building',
                'content_slug' => 'about-indonet',
                'order' => 1,
                'permissions' => null,
            ],
            [
                'name' => 'Training Materials',
                'type' => 'content_menu',
                'icon' => 'fas fa-graduation-cap',
                'content_slug' => 'analytics-training-materials',
                'order' => 2,
                'permissions' => null,
            ],
            [
                'name' => 'User Guide',
                'type' => 'content_menu',
                'icon' => 'fas fa-book-open',
                'content_slug' => 'user-guide-documentation',
                'order' => 3,
                'permissions' => null,
            ],
            [
                'name' => 'API Documentation',
                'type' => 'content_menu',
                'icon' => 'fas fa-code',
                'content_slug' => 'api-documentation',
                'order' => 4,
                'permissions' => ['data.view'],
            ],
        ]);

        // Create Administration submenu items (admin only)
        $this->createSubmenuItems($adminMenu->id, [
            [
                'name' => 'User Management',
                'type' => 'list_menu',
                'icon' => 'fas fa-users-cog',
                'route' => '/admin/users',
                'order' => 1,
                'permissions' => ['users.view'],
            ],
            [
                'name' => 'Role Management',
                'type' => 'list_menu',
                'icon' => 'fas fa-user-shield',
                'route' => '/admin/roles',
                'order' => 2,
                'permissions' => ['roles.view'],
            ],
            [
                'name' => 'Menu Management',
                'type' => 'list_menu',
                'icon' => 'fas fa-sitemap',
                'route' => '/admin/menus',
                'order' => 3,
                'permissions' => ['menus.view'],
            ],
            [
                'name' => 'Content Management',
                'type' => 'list_menu',
                'icon' => 'fas fa-edit',
                'route' => '/admin/content',
                'order' => 4,
                'permissions' => ['content.view'],
            ],
            [
                'name' => 'System Configuration',
                'type' => 'list_menu',
                'icon' => 'fas fa-cogs',
                'route' => '/admin/system-configuration',
                'order' => 5,
                'permissions' => ['admin.settings'],
            ],
            [
                'name' => 'System Status',
                'type' => 'content_menu',
                'icon' => 'fas fa-server',
                'content_slug' => 'system-status-health',
                'order' => 6,
                'permissions' => ['admin.view'],
            ],
        ]);

        // Create Support submenu items
        $this->createSubmenuItems($supportMenu->id, [
            [
                'name' => 'Contact Support',
                'type' => 'content_menu',
                'icon' => 'fas fa-headset',
                'content_slug' => 'contact-support',
                'order' => 1,
                'permissions' => null,
            ],
            [
                'name' => 'FAQ',
                'type' => 'content_menu',
                'icon' => 'fas fa-question-circle',
                'content_slug' => 'faq-troubleshooting',
                'order' => 2,
                'permissions' => null,
            ],
        ]);

        $this->command->info('Menu structure created successfully!');
    }

    /**
     * Helper method to create submenu items
     */
    private function createSubmenuItems($parentId, $items)
    {
        foreach ($items as $item) {
            $contentId = null;
            $routeOrUrl = $item['route'] ?? null;

            if (isset($item['content_slug'])) {
                $content = Content::where('slug', $item['content_slug'])->first();
                $contentId = $content?->id;
                $routeOrUrl = $content ? "/content/{$content->slug}" : null;
            }

            $menu = Menu::updateOrCreate([
                'parent_id' => $parentId,
                'name' => $item['name'],
                'type' => $item['type'],
                'order' => $item['order'],
            ], [
                'icon' => $item['icon'],
                'route_or_url' => $routeOrUrl,
                'content_id' => $contentId,
                'role_permissions_required' => $item['permissions'],
            ]);

            // Create children if specified
            if (isset($item['children'])) {
                $this->createSubmenuItems($menu->id, $item['children']);
            }
        }
    }

    // Content generation methods for each type of content
    private function getDashboardContent()
    {
        return '<div class="dashboard-overview">
            <h1><i class="fas fa-tachometer-alt"></i> Analytics Dashboard Overview</h1>
            <p class="lead">Welcome to the Indonet Analytics Hub - your comprehensive business intelligence platform.</p>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-chart-line text-primary"></i> Real-time Analytics</h5>
                            <p class="card-text">Access live performance metrics and KPIs across all business units.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-users text-success"></i> User Insights</h5>
                            <p class="card-text">Understand customer behavior and engagement patterns.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-dollar-sign text-warning"></i> Revenue Tracking</h5>
                            <p class="card-text">Monitor financial performance and growth metrics.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <h3>Quick Actions</h3>
            <ul>
                <li><a href="/analytics/sales"><i class="fas fa-chart-bar"></i> View Sales Performance</a></li>
                <li><a href="/reports/monthly"><i class="fas fa-calendar-alt"></i> Generate Monthly Report</a></li>
                <li><a href="/tools/export"><i class="fas fa-download"></i> Export Data</a></li>
            </ul>
        </div>';
    }

    private function getAboutContent()
    {
        return '<div class="about-content">
            <h1><i class="fas fa-building"></i> About Indonet</h1>
            <p class="lead">Leading Indonesia in digital transformation and business intelligence solutions.</p>
            
            <h3>Our Mission</h3>
            <p>To empower Indonesian businesses with cutting-edge analytics and data-driven insights that drive growth and innovation.</p>
            
            <h3>Our Vision</h3>
            <p>To be Southeast Asia\'s premier analytics and business intelligence platform, helping organizations make informed decisions through data.</p>
            
            <h3>Core Values</h3>
            <ul>
                <li><strong>Innovation:</strong> Continuously advancing our technology and methodologies</li>
                <li><strong>Integrity:</strong> Maintaining the highest standards of data security and privacy</li>
                <li><strong>Excellence:</strong> Delivering superior analytics solutions and customer service</li>
                <li><strong>Collaboration:</strong> Working together to achieve common goals</li>
            </ul>
            
            <h3>Contact Information</h3>
            <div class="contact-info">
                <p><i class="fas fa-envelope"></i> Email: info@indonet.co.id</p>
                <p><i class="fas fa-phone"></i> Phone: +62 21 1234 5678</p>
                <p><i class="fas fa-map-marker-alt"></i> Address: Jakarta, Indonesia</p>
            </div>
        </div>';
    }

    private function getSalesAnalyticsContent()
    {
        return '<div class="sales-analytics">
            <h1><i class="fas fa-chart-bar"></i> Sales Performance Analytics</h1>
            <p class="lead">Comprehensive sales performance tracking and analysis.</p>
            
            <div class="metrics-grid">
                <div class="metric-card">
                    <h3>Total Revenue</h3>
                    <p class="metric-value">$2,450,000</p>
                    <span class="metric-change positive">+15.3% vs last month</span>
                </div>
                <div class="metric-card">
                    <h3>Conversion Rate</h3>
                    <p class="metric-value">8.7%</p>
                    <span class="metric-change positive">+2.1% vs last month</span>
                </div>
                <div class="metric-card">
                    <h3>Average Deal Size</h3>
                    <p class="metric-value">$12,500</p>
                    <span class="metric-change negative">-3.2% vs last month</span>
                </div>
            </div>
            
            <h3>Sales Funnel Analysis</h3>
            <p>Track leads through each stage of your sales process:</p>
            <ul>
                <li>Prospects: 1,250 (+8.5%)</li>
                <li>Qualified Leads: 485 (+12.1%)</li>
                <li>Proposals: 156 (+6.7%)</li>
                <li>Closed Won: 89 (+15.3%)</li>
            </ul>
            
            <div class="action-buttons">
                <button class="btn btn-primary"><i class="fas fa-download"></i> Export Sales Report</button>
                <button class="btn btn-secondary"><i class="fas fa-chart-line"></i> View Detailed Analytics</button>
            </div>
        </div>';
    }

    private function getCustomerAnalyticsContent()
    {
        return '<div class="customer-analytics">
            <h1><i class="fas fa-users"></i> Customer Analytics Dashboard</h1>
            <p class="lead">Deep insights into customer behavior and engagement patterns.</p>
            
            <h3>Customer Overview</h3>
            <div class="customer-metrics">
                <div class="metric">
                    <h4>Total Customers</h4>
                    <span class="value">8,456</span>
                    <span class="change positive">+234 this month</span>
                </div>
                <div class="metric">
                    <h4>Active Users</h4>
                    <span class="value">6,123</span>
                    <span class="change positive">+12.5%</span>
                </div>
                <div class="metric">
                    <h4>Customer Retention</h4>
                    <span class="value">87.2%</span>
                    <span class="change positive">+2.1%</span>
                </div>
            </div>
            
            <h3>Customer Segmentation</h3>
            <ul>
                <li><strong>Enterprise:</strong> 15% of customers, 68% of revenue</li>
                <li><strong>SMB:</strong> 45% of customers, 25% of revenue</li>
                <li><strong>Startup:</strong> 40% of customers, 7% of revenue</li>
            </ul>
            
            <h3>Engagement Insights</h3>
            <p>Average session duration: 24 minutes</p>
            <p>Pages per session: 8.3</p>
            <p>Feature adoption rate: 72%</p>
        </div>';
    }

    private function getFinancialReportsContent()
    {
        return '<div class="financial-reports">
            <h1><i class="fas fa-dollar-sign"></i> Financial Reports Hub</h1>
            <p class="lead">Comprehensive financial analysis and reporting tools.</p>
            
            <h3>Revenue Summary</h3>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Period</th>
                        <th>Revenue</th>
                        <th>Growth</th>
                        <th>Profit Margin</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Q4 2024</td>
                        <td>$3,250,000</td>
                        <td>+18.5%</td>
                        <td>22.3%</td>
                    </tr>
                    <tr>
                        <td>Q3 2024</td>
                        <td>$2,750,000</td>
                        <td>+12.1%</td>
                        <td>20.8%</td>
                    </tr>
                    <tr>
                        <td>Q2 2024</td>
                        <td>$2,450,000</td>
                        <td>+8.9%</td>
                        <td>19.2%</td>
                    </tr>
                </tbody>
            </table>
            
            <h3>Financial KPIs</h3>
            <ul>
                <li>Annual Recurring Revenue (ARR): $12.8M</li>
                <li>Monthly Recurring Revenue (MRR): $1.1M</li>
                <li>Customer Acquisition Cost (CAC): $485</li>
                <li>Customer Lifetime Value (CLV): $8,750</li>
            </ul>
            
            <div class="report-actions">
                <a href="#" class="btn btn-primary"><i class="fas fa-file-pdf"></i> Download PDF Report</a>
                <a href="#" class="btn btn-success"><i class="fas fa-file-excel"></i> Export to Excel</a>
            </div>
        </div>';
    }

    private function getMonthlyReportContent()
    {
        return '<div class="monthly-report">
            <h1><i class="fas fa-calendar-alt"></i> Monthly Performance Report</h1>
            <p class="lead">Detailed monthly business performance analysis for ' . now()->format('F Y') . '</p>
            
            <h3>Executive Summary</h3>
            <p>This month showed strong performance across all key metrics, with particular strength in customer acquisition and revenue growth.</p>
            
            <h3>Key Achievements</h3>
            <ul>
                <li>Exceeded revenue target by 15.3%</li>
                <li>Acquired 234 new customers</li>
                <li>Launched 3 new product features</li>
                <li>Achieved 99.8% uptime</li>
            </ul>
            
            <h3>Metrics Breakdown</h3>
            <div class="metrics-table">
                <table class="table">
                    <tr><td>Revenue</td><td>$2,450,000</td><td class="positive">+15.3%</td></tr>
                    <tr><td>New Customers</td><td>234</td><td class="positive">+28.1%</td></tr>
                    <tr><td>Churn Rate</td><td>2.1%</td><td class="negative">+0.3%</td></tr>
                    <tr><td>Support Tickets</td><td>156</td><td class="positive">-12.5%</td></tr>
                </table>
            </div>
            
            <h3>Next Month Focus</h3>
            <ul>
                <li>Product feature enhancement</li>
                <li>Customer success program expansion</li>
                <li>Market expansion initiative</li>
            </ul>
        </div>';
    }

    private function getQuarterlyReportContent()
    {
        return '<div class="quarterly-report">
            <h1><i class="fas fa-calendar-check"></i> Quarterly Business Review</h1>
            <p class="lead">Comprehensive quarterly analysis for Q4 2024</p>
            
            <h3>Quarter Highlights</h3>
            <ul>
                <li>Revenue grew 45% year-over-year</li>
                <li>Expanded to 3 new markets</li>
                <li>Launched enterprise product tier</li>
                <li>Team grew by 25 new hires</li>
            </ul>
            
            <h3>Financial Performance</h3>
            <p><strong>Revenue:</strong> $7.2M (+45% YoY)</p>
            <p><strong>Gross Profit:</strong> $5.8M (80.5% margin)</p>
            <p><strong>Net Income:</strong> $1.2M (16.7% margin)</p>
            
            <h3>Operational Metrics</h3>
            <p><strong>Customer Growth:</strong> 2,145 new customers</p>
            <p><strong>Product Usage:</strong> +67% feature adoption</p>
            <p><strong>Team Productivity:</strong> +23% efficiency improvement</p>
            
            <h3>Strategic Initiatives</h3>
            <p>Successfully completed 8 of 10 planned strategic initiatives, with focus on product development and market expansion.</p>
        </div>';
    }

    private function getTrainingContent()
    {
        return '<div class="training-materials">
            <h1><i class="fas fa-graduation-cap"></i> Analytics Training Materials</h1>
            <p class="lead">Comprehensive training resources to master the Analytics Hub platform.</p>
            
            <h3>Getting Started</h3>
            <ul>
                <li><a href="#"><i class="fas fa-play-circle"></i> Platform Overview (Video)</a></li>
                <li><a href="#"><i class="fas fa-file-pdf"></i> Quick Start Guide (PDF)</a></li>
                <li><a href="#"><i class="fas fa-book"></i> User Manual (Interactive)</a></li>
            </ul>
            
            <h3>Advanced Features</h3>
            <ul>
                <li><a href="#"><i class="fas fa-chart-line"></i> Custom Dashboard Creation</a></li>
                <li><a href="#"><i class="fas fa-filter"></i> Advanced Data Filtering</a></li>
                <li><a href="#"><i class="fas fa-code"></i> API Integration Guide</a></li>
                <li><a href="#"><i class="fas fa-share-alt"></i> Report Sharing & Collaboration</a></li>
            </ul>
            
            <h3>Video Tutorials</h3>
            <div class="video-grid">
                <div class="video-card">
                    <h4>Creating Your First Dashboard</h4>
                    <p>Learn to build custom dashboards from scratch</p>
                    <span class="duration">12:35</span>
                </div>
                <div class="video-card">
                    <h4>Data Export Masterclass</h4>
                    <p>Master all export formats and automation</p>
                    <span class="duration">18:42</span>
                </div>
                <div class="video-card">
                    <h4>Advanced Analytics Techniques</h4>
                    <p>Unlock the power of predictive analytics</p>
                    <span class="duration">25:18</span>
                </div>
            </div>
        </div>';
    }

    private function getUserGuideContent()
    {
        return '<div class="user-guide">
            <h1><i class="fas fa-book-open"></i> User Guide & Documentation</h1>
            <p class="lead">Complete documentation for the Analytics Hub platform.</p>
            
            <h3>Table of Contents</h3>
            <ol>
                <li><a href="#getting-started">Getting Started</a></li>
                <li><a href="#navigation">Platform Navigation</a></li>
                <li><a href="#dashboards">Working with Dashboards</a></li>
                <li><a href="#reports">Creating Reports</a></li>
                <li><a href="#data-management">Data Management</a></li>
                <li><a href="#user-management">User Management</a></li>
                <li><a href="#troubleshooting">Troubleshooting</a></li>
            </ol>
            
            <h3 id="getting-started">Getting Started</h3>
            <p>Welcome to the Analytics Hub! This guide will help you navigate and use all features of the platform effectively.</p>
            
            <h4>System Requirements</h4>
            <ul>
                <li>Modern web browser (Chrome, Firefox, Safari, Edge)</li>
                <li>JavaScript enabled</li>
                <li>Minimum screen resolution: 1024x768</li>
            </ul>
            
            <h4>First Login</h4>
            <p>After receiving your invitation email, click the activation link to set up your account and password.</p>
            
            <h3 id="navigation">Platform Navigation</h3>
            <p>The main navigation menu provides access to all platform features:</p>
            <ul>
                <li><strong>Dashboard:</strong> Your personalized overview</li>
                <li><strong>Analytics:</strong> Detailed analysis tools</li>
                <li><strong>Reports:</strong> Pre-built and custom reports</li>
                <li><strong>Tools:</strong> Data export and utilities</li>
            </ul>
        </div>';
    }

    private function getDataExportContent()
    {
        return '<div class="data-export-tools">
            <h1><i class="fas fa-download"></i> Data Export Tools</h1>
            <p class="lead">Export your analytics data in multiple formats for external analysis.</p>
            
            <div class="export-options">
                <div class="export-card">
                    <h3><i class="fas fa-file-csv"></i> CSV Export</h3>
                    <p>Export raw data in comma-separated values format</p>
                    <button class="btn btn-primary">Export CSV</button>
                </div>
                
                <div class="export-card">
                    <h3><i class="fas fa-file-excel"></i> Excel Export</h3>
                    <p>Export formatted data with charts and formulas</p>
                    <button class="btn btn-success">Export Excel</button>
                </div>
                
                <div class="export-card">
                    <h3><i class="fas fa-file-pdf"></i> PDF Report</h3>
                    <p>Generate formatted PDF reports with visualizations</p>
                    <button class="btn btn-danger">Export PDF</button>
                </div>
                
                <div class="export-card">
                    <h3><i class="fas fa-code"></i> JSON/API</h3>
                    <p>Programmatic data access via REST API</p>
                    <button class="btn btn-info">View API Docs</button>
                </div>
            </div>
            
            <h3>Export Settings</h3>
            <form class="export-form">
                <div class="form-group">
                    <label>Date Range:</label>
                    <select class="form-control">
                        <option>Last 7 days</option>
                        <option>Last 30 days</option>
                        <option>Last 3 months</option>
                        <option>Custom range</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Data Granularity:</label>
                    <select class="form-control">
                        <option>Daily</option>
                        <option>Weekly</option>
                        <option>Monthly</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Include:</label>
                    <div class="checkbox-group">
                        <input type="checkbox" checked> Revenue Data
                        <input type="checkbox" checked> Customer Data
                        <input type="checkbox"> User Activity
                        <input type="checkbox"> System Logs
                    </div>
                </div>
            </form>
        </div>';
    }

    private function getReportGeneratorContent()
    {
        return '<div class="report-generator">
            <h1><i class="fas fa-hammer"></i> Report Generator</h1>
            <p class="lead">Create custom reports tailored to your specific needs.</p>
            
            <div class="report-builder">
                <h3>Build Your Report</h3>
                
                <div class="step">
                    <h4>Step 1: Select Data Source</h4>
                    <div class="data-sources">
                        <div class="source-option">
                            <input type="radio" name="source" value="sales"> Sales Data
                        </div>
                        <div class="source-option">
                            <input type="radio" name="source" value="customers"> Customer Data
                        </div>
                        <div class="source-option">
                            <input type="radio" name="source" value="analytics"> Analytics Data
                        </div>
                        <div class="source-option">
                            <input type="radio" name="source" value="financial"> Financial Data
                        </div>
                    </div>
                </div>
                
                <div class="step">
                    <h4>Step 2: Choose Metrics</h4>
                    <div class="metrics-selection">
                        <input type="checkbox"> Revenue
                        <input type="checkbox"> Conversion Rate
                        <input type="checkbox"> Customer Count
                        <input type="checkbox"> Average Order Value
                        <input type="checkbox"> Retention Rate
                        <input type="checkbox"> Growth Rate
                    </div>
                </div>
                
                <div class="step">
                    <h4>Step 3: Set Filters</h4>
                    <div class="filters">
                        <label>Date Range:</label>
                        <input type="date" placeholder="Start Date">
                        <input type="date" placeholder="End Date">
                        
                        <label>Region:</label>
                        <select>
                            <option>All Regions</option>
                            <option>Jakarta</option>
                            <option>Surabaya</option>
                            <option>Bandung</option>
                        </select>
                    </div>
                </div>
                
                <div class="step">
                    <h4>Step 4: Choose Visualization</h4>
                    <div class="chart-types">
                        <div class="chart-option">
                            <i class="fas fa-chart-line"></i> Line Chart
                        </div>
                        <div class="chart-option">
                            <i class="fas fa-chart-bar"></i> Bar Chart
                        </div>
                        <div class="chart-option">
                            <i class="fas fa-chart-pie"></i> Pie Chart
                        </div>
                        <div class="chart-option">
                            <i class="fas fa-table"></i> Data Table
                        </div>
                    </div>
                </div>
                
                <div class="actions">
                    <button class="btn btn-primary">Generate Report</button>
                    <button class="btn btn-secondary">Save Template</button>
                    <button class="btn btn-outline">Preview</button>
                </div>
            </div>
        </div>';
    }

    private function getSystemStatusContent()
    {
        return '<div class="system-status">
            <h1><i class="fas fa-server"></i> System Status & Health</h1>
            <p class="lead">Real-time system monitoring and health indicators.</p>
            
            <div class="status-overview">
                <div class="status-card healthy">
                    <h3>Overall Status</h3>
                    <span class="status-indicator">✅ Operational</span>
                    <p>All systems running normally</p>
                </div>
                
                <div class="status-card">
                    <h3>Uptime</h3>
                    <span class="metric">99.97%</span>
                    <p>Last 30 days</p>
                </div>
                
                <div class="status-card">
                    <h3>Response Time</h3>
                    <span class="metric">245ms</span>
                    <p>Average API response</p>
                </div>
            </div>
            
            <h3>System Components</h3>
            <div class="components-list">
                <div class="component">
                    <span class="component-name">Web Application</span>
                    <span class="status healthy">✅ Operational</span>
                </div>
                <div class="component">
                    <span class="component-name">Database</span>
                    <span class="status healthy">✅ Operational</span>
                </div>
                <div class="component">
                    <span class="component-name">API Gateway</span>
                    <span class="status healthy">✅ Operational</span>
                </div>
                <div class="component">
                    <span class="component-name">Background Jobs</span>
                    <span class="status healthy">✅ Operational</span>
                </div>
                <div class="component">
                    <span class="component-name">File Storage</span>
                    <span class="status healthy">✅ Operational</span>
                </div>
            </div>
            
            <h3>Recent Incidents</h3>
            <div class="incidents">
                <p class="no-incidents">No incidents in the last 30 days</p>
            </div>
            
            <h3>Scheduled Maintenance</h3>
            <div class="maintenance">
                <p>Next scheduled maintenance: Sunday, 2:00 AM - 4:00 AM UTC</p>
                <p>Estimated downtime: 30 minutes</p>
            </div>
        </div>';
    }

    private function getApiDocumentationContent()
    {
        return '<div class="api-documentation">
            <h1><i class="fas fa-code"></i> API Documentation</h1>
            <p class="lead">Complete reference for the Analytics Hub REST API.</p>
            
            <h3>Getting Started</h3>
            <p>The Analytics Hub API provides programmatic access to your analytics data and platform features.</p>
            
            <h4>Authentication</h4>
            <p>All API requests require authentication using Bearer tokens:</p>
            <pre><code>Authorization: Bearer YOUR_API_TOKEN</code></pre>
            
            <h4>Base URL</h4>
            <pre><code>https://api.indonet.co.id/v1/</code></pre>
            
            <h3>Endpoints</h3>
            
            <h4>Analytics</h4>
            <div class="endpoint">
                <span class="method get">GET</span>
                <span class="url">/analytics/dashboard</span>
                <p>Retrieve dashboard metrics</p>
            </div>
            
            <div class="endpoint">
                <span class="method get">GET</span>
                <span class="url">/analytics/sales</span>
                <p>Get sales performance data</p>
            </div>
            
            <div class="endpoint">
                <span class="method get">GET</span>
                <span class="url">/analytics/customers</span>
                <p>Retrieve customer analytics</p>
            </div>
            
            <h4>Reports</h4>
            <div class="endpoint">
                <span class="method get">GET</span>
                <span class="url">/reports</span>
                <p>List available reports</p>
            </div>
            
            <div class="endpoint">
                <span class="method post">POST</span>
                <span class="url">/reports/generate</span>
                <p>Generate custom report</p>
            </div>
            
            <h4>Data Export</h4>
            <div class="endpoint">
                <span class="method post">POST</span>
                <span class="url">/export/csv</span>
                <p>Export data as CSV</p>
            </div>
            
            <div class="endpoint">
                <span class="method post">POST</span>
                <span class="url">/export/json</span>
                <p>Export data as JSON</p>
            </div>
            
            <h3>Rate Limits</h3>
            <ul>
                <li>1000 requests per hour for authenticated users</li>
                <li>100 requests per hour for unauthenticated requests</li>
                <li>Special limits may apply to export endpoints</li>
            </ul>
            
            <h3>Error Handling</h3>
            <p>The API uses standard HTTP status codes and returns detailed error messages in JSON format.</p>
        </div>';
    }

    private function getContactSupportContent()
    {
        return '<div class="contact-support">
            <h1><i class="fas fa-headset"></i> Contact Support</h1>
            <p class="lead">Get help when you need it. Our support team is here to assist you.</p>
            
            <div class="support-options">
                <div class="support-card">
                    <h3><i class="fas fa-comments"></i> Live Chat</h3>
                    <p>Get instant help from our support team</p>
                    <p><strong>Hours:</strong> Monday-Friday, 9 AM - 6 PM WIB</p>
                    <button class="btn btn-primary">Start Chat</button>
                </div>
                
                <div class="support-card">
                    <h3><i class="fas fa-envelope"></i> Email Support</h3>
                    <p>Send us a detailed message about your issue</p>
                    <p><strong>Response time:</strong> Within 4 hours</p>
                    <a href="mailto:support@indonet.co.id" class="btn btn-secondary">Send Email</a>
                </div>
                
                <div class="support-card">
                    <h3><i class="fas fa-phone"></i> Phone Support</h3>
                    <p>Call us for urgent issues</p>
                    <p><strong>Phone:</strong> +62 21 1234 5678</p>
                    <p><strong>Hours:</strong> 24/7 for critical issues</p>
                </div>
            </div>
            
            <h3>Before Contacting Support</h3>
            <ul>
                <li>Check our <a href="/support/faq">FAQ section</a> for quick answers</li>
                <li>Search the <a href="/resources/user-guide">User Guide</a> for detailed instructions</li>
                <li>Try clearing your browser cache and cookies</li>
                <li>Ensure you have the latest browser version</li>
            </ul>
            
            <h3>When Contacting Support, Please Include:</h3>
            <ul>
                <li>Your username and account details</li>
                <li>Detailed description of the issue</li>
                <li>Steps you took before the issue occurred</li>
                <li>Browser type and version</li>
                <li>Screenshots or error messages (if applicable)</li>
            </ul>
            
            <h3>Emergency Support</h3>
            <p>For critical system outages or security issues, call our emergency hotline:</p>
            <p><strong>Emergency:</strong> +62 21 9999 0000 (24/7)</p>
        </div>';
    }

    private function getFaqContent()
    {
        return '<div class="faq-content">
            <h1><i class="fas fa-question-circle"></i> FAQ & Troubleshooting</h1>
            <p class="lead">Find answers to commonly asked questions and solutions to common issues.</p>
            
            <h3>General Questions</h3>
            
            <div class="faq-item">
                <h4>How do I reset my password?</h4>
                <p>Click the "Forgot Password" link on the login page and follow the instructions sent to your email.</p>
            </div>
            
            <div class="faq-item">
                <h4>Can I export data from the platform?</h4>
                <p>Yes, you can export data in multiple formats (CSV, Excel, PDF) from the Tools menu. Your export permissions depend on your user role.</p>
            </div>
            
            <div class="faq-item">
                <h4>How often is the data updated?</h4>
                <p>Most analytics data is updated in real-time. Some complex reports may have a delay of up to 15 minutes.</p>
            </div>
            
            <div class="faq-item">
                <h4>Who can access the Admin panel?</h4>
                <p>Only users with Administrator or Super Administrator roles can access admin features.</p>
            </div>
            
            <h3>Technical Issues</h3>
            
            <div class="faq-item">
                <h4>The page is loading slowly. What should I do?</h4>
                <p>Try refreshing the page, clearing your browser cache, or switching to a different browser. Contact support if issues persist.</p>
            </div>
            
            <div class="faq-item">
                <h4>I\'m getting a "Permission Denied" error. What does this mean?</h4>
                <p>This means you don\'t have sufficient permissions to access that feature. Contact your administrator to request access.</p>
            </div>
            
            <div class="faq-item">
                <h4>Charts are not displaying properly. How can I fix this?</h4>
                <p>Ensure JavaScript is enabled in your browser and try disabling browser extensions. Update to the latest browser version.</p>
            </div>
            
            <div class="faq-item">
                <h4>Can I use the platform on mobile devices?</h4>
                <p>Yes, the platform is mobile-responsive and works on tablets and smartphones, though some features are optimized for desktop use.</p>
            </div>
            
            <h3>Account & Billing</h3>
            
            <div class="faq-item">
                <h4>How do I update my profile information?</h4>
                <p>Click on your profile icon in the top right corner and select "Profile Settings" to update your information.</p>
            </div>
            
            <div class="faq-item">
                <h4>Can I invite other users to the platform?</h4>
                <p>Yes, if you have user management permissions, you can invite users through the Admin panel under User Management.</p>
            </div>
            
            <h3>Still Need Help?</h3>
            <p>If you can\'t find the answer you\'re looking for, please <a href="/support/contact">contact our support team</a>. We\'re here to help!</p>
        </div>';
    }
}
