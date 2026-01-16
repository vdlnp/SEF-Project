<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Completed Reviews</title>

    <style>
        body {
            margin: 0;
            font-family: "Segoe UI", Arial, sans-serif;
            background: #1f1d29; 
            color: #e6e6e6;
        }

        header {
            background: #1abc9c; 
            color: white;
            padding: 16px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        }

        header h1 {
            margin: 0;
            font-size: 22px;
            letter-spacing: 0.5px;
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .back-btn, .logout {
            text-decoration: none;
            color: white;
            font-weight: 600;
            border: 1px solid rgba(255,255,255,0.6);
            padding: 6px 16px;
            border-radius: 20px;
            transition: all 0.3s ease;
        }

        .back-btn:hover, .logout:hover {
            background: #2e1b36;
            box-shadow: 0 0 15px rgba(26,188,156,0.7), 0 0 25px rgba(155,89,182,0.5);
            transform: scale(1.08);
        }

        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .section-title {
            font-size: 20px;
            font-weight: 600;
            color: #1abc9c;
            margin-bottom: 20px;
            border-bottom: 2px solid #2c2a38;
            padding-bottom: 10px;
        }

        .completed-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .completed-card {
            background: #2c2a38;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.4);
            transition: all 0.3s ease;
            border-left: 4px solid #27ae60;
        }

        .completed-card:hover {
            transform: translateX(8px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.6);
        }

        .proposal-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 12px;
        }

        .proposal-title {
            font-size: 18px;
            font-weight: 600;
            color: #e6e6e6;
            margin-bottom: 8px;
        }

        .proposal-id {
            font-size: 13px;
            color: #888;
        }

        .review-score {
            display: inline-block;
            background: #1abc9c;
            color: white;
            padding: 6px 16px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 16px;
        }

        .proposal-info {
            display: flex;
            gap: 30px;
            margin-bottom: 16px;
            font-size: 14px;
            color: #bbb;
        }

        .proposal-description {
            color: #cfcfcf;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 16px;
        }

        .review-comment {
            background: #1f1d29;
            padding: 12px;
            border-radius: 6px;
            margin-top: 12px;
            color: #cfcfcf;
            font-size: 14px;
            line-height: 1.5;
            font-style: italic;
        }

        footer {
            background: #161421;
            color: #bbb;
            text-align: center;
            padding: 14px;
            font-size: 14px;
            border-top: 1px solid #333333;
            margin-top: 60px;
        }

        footer span {
            font-weight: 600;
        }
    </style>
</head>

<body>

<header>
    <h1>Completed Reviews</h1>

    <div class="nav-right">
        <a href="reviewer_main.html" class="back-btn">← Back</a>
        <a href="login.html" class="logout">Logout</a>
    </div>
</header>

<div class="container">
    <div class="section-title">Your Completed Reviews</div>

    <div class="completed-list" id="completedList">
        <!-- Completed reviews will be loaded here -->
    </div>
</div>

<footer>
    <span>Project Bidding System</span> | Reviewer Portal
</footer>

<script>
    const completedReviews = [
        {
            id: "PROP-2025-087",
            title: "Healthcare Management System Integration",
            submittedBy: "MediTech Solutions",
            submittedDate: "2025-12-15",
            reviewedDate: "2026-01-05",
            score: 8.5,
            comments: "Strong technical approach with comprehensive security measures. The integration architecture is well-designed. Recommend approval with minor adjustments to the timeline for Phase 2 implementation.",
            description: "Integration of existing healthcare management systems with new patient portal and telemedicine capabilities."
        },
        {
            id: "PROP-2025-092",
            title: "Retail Analytics Platform",
            submittedBy: "DataVision Corp",
            submittedDate: "2025-12-20",
            reviewedDate: "2026-01-08",
            score: 7.2,
            comments: "Good overall proposal with solid data pipeline design. However, the machine learning models need more detailed documentation. Budget allocation for cloud resources appears optimistic and should be reviewed.",
            description: "Development of advanced retail analytics platform with predictive inventory management and customer behavior analysis."
        },
        {
            id: "PROP-2025-089",
            title: "Smart City IoT Infrastructure",
            submittedBy: "Urban Tech Innovations",
            submittedDate: "2025-12-18",
            reviewedDate: "2026-01-03",
            score: 9.1,
            comments: "Exceptional proposal with innovative approach to sensor network deployment. Energy efficiency considerations are particularly impressive. Strong team credentials and realistic project milestones. Highly recommend for approval.",
            description: "Deployment of IoT sensor network for traffic management, environmental monitoring, and public safety systems across metropolitan area."
        }
    ];

    function loadCompletedReviews() {
        const completedList = document.getElementById('completedList');
        completedList.innerHTML = '';

        completedReviews.forEach(review => {
            const card = document.createElement('div');
            card.className = 'completed-card';
            card.innerHTML = `
                <div class="proposal-header">
                    <div>
                        <div class="proposal-title">${review.title}</div>
                        <div class="proposal-id">${review.id}</div>
                    </div>
                    <span class="review-score">${review.score}/10</span>
                </div>
                <div class="proposal-info">
                    <span>👤 ${review.submittedBy}</span>
                    <span>📅 Submitted: ${review.submittedDate}</span>
                    <span>✅ Reviewed: ${review.reviewedDate}</span>
                </div>
                <div class="proposal-description">
                    ${review.description}
                </div>
                <div class="review-comment">
                    💬 ${review.comments}
                </div>
            `;
            completedList.appendChild(card);
        });
    }

    loadCompletedReviews();
</script>

</body>
</html>