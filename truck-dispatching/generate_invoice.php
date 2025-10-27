<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

require_once 'includes/database.php';

if (isset($_GET['dispatch_id'])) {
    $dispatch_id = $_GET['dispatch_id'];
    
    // Get complete data for invoice
    $dispatch_data = $conn->query("SELECT d.*, t.registration_no, t.model as truck_model, 
                                  dr.name as driver_name, c.company_name, c.contact_person, 
                                  c.email as client_email, c.phone as client_phone, c.address as client_address
                           FROM dispatches d 
                           LEFT JOIN trucks t ON d.truck_id = t.id 
                           LEFT JOIN drivers dr ON d.driver_id = dr.id 
                           LEFT JOIN clients c ON d.client_id = c.id 
                           WHERE d.id='$dispatch_id'")->fetch_assoc();
    
    if (!$dispatch_data) {
        die("Dispatch not found!");
    }
    
    // Create simple HTML invoice that user can print
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Invoice - <?php echo $dispatch_data['dispatch_id']; ?></title>
        <style>
            body { 
                font-family: Arial, sans-serif; 
                margin: 0; 
                padding: 20px; 
                color: #333; 
            }
            .invoice-container { 
                max-width: 800px; 
                margin: 0 auto; 
                border: 2px solid #2c3e50; 
                padding: 30px; 
                background: white; 
            }
            .header { 
                border-bottom: 3px solid #3498db; 
                padding-bottom: 20px; 
                margin-bottom: 30px; 
                display: flex; 
                justify-content: space-between; 
                align-items: flex-start; 
            }
            .company-info h1 { 
                color: #2c3e50; 
                margin: 0 0 10px 0; 
            }
            .invoice-info { 
                text-align: right; 
            }
            .invoice-info h2 { 
                color: #3498db; 
                margin: 0 0 10px 0; 
            }
            .billing-section { 
                display: grid; 
                grid-template-columns: 1fr 1fr; 
                gap: 30px; 
                margin: 30px 0; 
            }
            .section-title { 
                color: #2c3e50; 
                border-bottom: 2px solid #ecf0f1; 
                padding-bottom: 10px; 
                margin-bottom: 15px; 
            }
            .items-table { 
                width: 100%; 
                border-collapse: collapse; 
                margin: 30px 0; 
            }
            .items-table th { 
                background: #34495e; 
                color: white; 
                padding: 12px; 
                text-align: left; 
            }
            .items-table td { 
                padding: 12px; 
                border-bottom: 1px solid #ecf0f1; 
            }
            .total-section { 
                text-align: right; 
                margin-top: 30px; 
            }
            .total-row { 
                display: inline-block; 
                text-align: left; 
                min-width: 300px; 
            }
            .grand-total { 
                font-size: 20px; 
                font-weight: bold; 
                color: #2c3e50; 
                border-top: 3px solid #3498db; 
                padding-top: 10px; 
                margin-top: 10px; 
            }
            .footer { 
                margin-top: 50px; 
                text-align: center; 
                color: #7f8c8d; 
                border-top: 1px solid #bdc3c7; 
                padding-top: 20px; 
            }
            .print-btn { 
                background: #3498db; 
                color: white; 
                padding: 12px 24px; 
                border: none; 
                border-radius: 5px; 
                cursor: pointer; 
                font-size: 16px; 
                margin-bottom: 20px; 
            }
            .print-btn:hover { 
                background: #2980b9; 
            }
            @media print {
                .print-btn { display: none; }
                body { padding: 0; }
            }
        </style>
    </head>
    <body>
        <div style="text-align: center; margin-bottom: 20px;">
            <button class="print-btn" onclick="window.print()">🖨️ Print Invoice</button>
        </div>
        
        <div class="invoice-container">
            <!-- Header -->
            <div class="header">
                <div class="company-info">
                    <h1>🚛 TRUCK DISPATCHING CO.</h1>
                    <p style="margin: 5px 0; color: #7f8c8d;">
                        123 Logistics Street, City, State 12345<br>
                        📞 (555) 123-4567 | ✉️ billing@truckdispatching.com
                    </p>
                </div>
                <div class="invoice-info">
                    <h2>INVOICE</h2>
                    <p style="margin: 5px 0;">
                        <strong>Invoice #:</strong> <?php echo $dispatch_data['dispatch_id']; ?><br>
                        <strong>Date:</strong> <?php echo date('F j, Y'); ?><br>
                        <strong>Due Date:</strong> <?php echo date('F j, Y', strtotime('+30 days')); ?>
                    </p>
                </div>
            </div>

            <!-- Billing Information -->
            <div class="billing-section">
                <div>
                    <h3 class="section-title">Bill To:</h3>
                    <p>
                        <strong><?php echo $dispatch_data['company_name']; ?></strong><br>
                        <?php if ($dispatch_data['contact_person']): ?>
                            Attn: <?php echo $dispatch_data['contact_person']; ?><br>
                        <?php endif; ?>
                        <?php if ($dispatch_data['client_address']): ?>
                            <?php echo $dispatch_data['client_address']; ?><br>
                        <?php endif; ?>
                        <?php if ($dispatch_data['client_phone']): ?>
                            📞 <?php echo $dispatch_data['client_phone']; ?><br>
                        <?php endif; ?>
                        <?php if ($dispatch_data['client_email']): ?>
                            ✉️ <?php echo $dispatch_data['client_email']; ?>
                        <?php endif; ?>
                    </p>
                </div>
                <div>
                    <h3 class="section-title">Dispatch Details:</h3>
                    <p>
                        <strong>Driver:</strong> <?php echo $dispatch_data['driver_name']; ?><br>
                        <strong>Truck:</strong> <?php echo $dispatch_data['registration_no']; ?> - <?php echo $dispatch_data['truck_model']; ?><br>
                        <strong>Delivery Date:</strong> <?php echo date('F j, Y', strtotime($dispatch_data['created_at'])); ?><br>
                        <strong>Status:</strong> <span style="color: #27ae60; font-weight: bold;">DELIVERED</span>
                    </p>
                </div>
            </div>

            <!-- Route Information -->
            <div>
                <h3 class="section-title">Route Information</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <strong>📍 Pickup Location:</strong><br>
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-top: 5px;">
                            <?php echo nl2br($dispatch_data['pickup_address']); ?>
                        </div>
                    </div>
                    <div>
                        <strong>🎯 Delivery Location:</strong><br>
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-top: 5px;">
                            <?php echo nl2br($dispatch_data['delivery_address']); ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <table class="items-table">
                <thead>
                    <tr>
                        <th width="50%">Description</th>
                        <th width="15%">Quantity</th>
                        <th width="15%">Weight</th>
                        <th width="20%">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <strong>Freight Services - <?php echo $dispatch_data['load_type']; ?></strong><br>
                            <small style="color: #7f8c8d;">Transportation from pickup to delivery location</small>
                        </td>
                        <td><?php echo $dispatch_data['load_quantity']; ?> units</td>
                        <td><?php echo $dispatch_data['load_weight']; ?> tons</td>
                        <td><strong>$<?php echo number_format($dispatch_data['total_cost'], 2); ?></strong></td>
                    </tr>
                </tbody>
            </table>

            <!-- Totals -->
            <div class="total-section">
                <div class="total-row">
                    <div style="display: flex; justify-content: space-between; margin: 5px 0;">
                        <span>Subtotal:</span>
                        <span>$<?php echo number_format($dispatch_data['total_cost'], 2); ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin: 5px 0;">
                        <span>Tax (0%):</span>
                        <span>$0.00</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin: 5px 0; padding-top: 10px; border-top: 2px solid #3498db;" class="grand-total">
                        <span>TOTAL DUE:</span>
                        <span>$<?php echo number_format($dispatch_data['total_cost'], 2); ?></span>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p>
                    <strong>Payment Terms:</strong> Net 30 Days | 
                    <strong>Payment Methods:</strong> Bank Transfer, Credit Card, Check<br>
                    Thank you for your business! Questions? Contact us at billing@truckdispatching.com
                </p>
                <p style="margin-top: 10px; font-size: 12px;">
                    Computer-generated invoice. No signature required.
                </p>
            </div>
        </div>

        <script>
            // Auto-print option (optional)
            // window.onload = function() {
            //     setTimeout(function() {
            //         window.print();
            //     }, 1000);
            // };
        </script>
    </body>
    </html>
    <?php
} else {
    header('Location: dispatches.php');
    exit;
}
?>