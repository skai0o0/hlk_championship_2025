<?php
declare(strict_types=1);

require_once 'db_connect.php';

// Start session
session_start();

header('Content-Type: application/json');

// Function to check if user is logged in
function isLoggedIn(): bool {
    if (isset($_SESSION['hlk_user_id']) && is_numeric($_SESSION['hlk_user_id'])) {
        return true;
    }
    if (!empty($_COOKIE['hlk_user_id']) && ctype_digit($_COOKIE['hlk_user_id'])) {
        return true;
    }
    return false;
}

// Function to get current user ID
function getCurrentUserId(): ?int {
    if (isset($_SESSION['hlk_user_id']) && is_numeric($_SESSION['hlk_user_id'])) {
        return (int)$_SESSION['hlk_user_id'];
    }
    if (!empty($_COOKIE['hlk_user_id']) && ctype_digit($_COOKIE['hlk_user_id'])) {
        return (int)$_COOKIE['hlk_user_id'];
    }
    return null;
}

// Function to generate unique 6-digit team code
function generateTeamCode($conn): string {
    do {
        $code = str_pad((string)rand(100000, 999999), 6, '0', STR_PAD_LEFT);
        $stmt = $conn->prepare("SELECT id FROM teams WHERE team_code = ?");
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $result = $stmt->get_result();
    } while ($result->num_rows > 0);
    
    return $code;
}

// Function to check if user is already in a team
function isUserInTeam($conn, int $userId): bool {
    $stmt = $conn->prepare("SELECT id FROM team_members WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

// Function to get team member count
function getTeamMemberCount($conn, int $teamId): int {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM team_members WHERE team_id = ?");
    $stmt->bind_param("i", $teamId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return (int)$row['count'];
}

// Function to get user's team information
function getUserTeam($conn, int $userId): ?array {
    $stmt = $conn->prepare("
        SELECT t.*, tm.role, tm.joined_at
        FROM teams t
        JOIN team_members tm ON t.id = tm.team_id
        WHERE tm.user_id = ?
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return null;
    }
    
    $team = $result->fetch_assoc();
    
    // Get team members
    $stmt = $conn->prepare("
        SELECT u.id, u.full_name, u.class, tm.role, tm.joined_at
        FROM team_members tm
        JOIN users u ON tm.user_id = u.id
        WHERE tm.team_id = ?
        ORDER BY tm.role DESC, tm.joined_at ASC
    ");
    $stmt->bind_param("i", $team['id']);
    $stmt->execute();
    $membersResult = $stmt->get_result();
    
    $members = [];
    while ($member = $membersResult->fetch_assoc()) {
        $members[] = $member;
    }
    
    $team['members'] = $members;
    $team['member_count'] = count($members);
    
    return $team;
}

// Handle different actions
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'create':
        if (!isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để tạo đội!']);
            exit;
        }
        
        $userId = getCurrentUserId();
        if ($userId === null) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để tạo đội!']);
            exit;
        }
        $teamName = trim($_POST['team_name'] ?? '');
        $teamDescription = trim($_POST['team_description'] ?? '');
        
        if (empty($teamName)) {
            echo json_encode(['success' => false, 'message' => 'Tên đội không được để trống!']);
            exit;
        }
        
        if (isUserInTeam($conn, $userId)) {
            echo json_encode(['success' => false, 'message' => 'Bạn đã có đội rồi! Mỗi người chỉ có thể tham gia một đội.']);
            exit;
        }
        
        try {
            $conn->begin_transaction();
            
            // Create team
            $teamCode = generateTeamCode($conn);
            $stmt = $conn->prepare("INSERT INTO teams (team_code, team_name, team_description, leader_id) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sssi", $teamCode, $teamName, $teamDescription, $userId);
            $stmt->execute();
            
            $teamId = $conn->insert_id;
            
            // Add leader as team member
            $stmt = $conn->prepare("INSERT INTO team_members (team_id, user_id, role) VALUES (?, ?, 'leader')");
            $stmt->bind_param("ii", $teamId, $userId);
            $stmt->execute();
            
            $conn->commit();
            
            // Get created team info
            $teamInfo = getUserTeam($conn, $userId);
            
            echo json_encode([
                'success' => true,
                'message' => 'Tạo đội thành công!',
                'team' => $teamInfo
            ]);
            
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => 'Lỗi tạo đội: ' . $e->getMessage()]);
        }
        break;
        
    case 'join':
        if (!isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để tham gia đội!']);
            exit;
        }
        
        $userId = getCurrentUserId();
        if ($userId === null) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để tham gia đội!']);
            exit;
        }
        $teamCode = trim($_POST['team_code'] ?? '');
        
        if (empty($teamCode) || strlen($teamCode) !== 6) {
            echo json_encode(['success' => false, 'message' => 'Mã đội phải có 6 chữ số!']);
            exit;
        }
        
        if (isUserInTeam($conn, $userId)) {
            echo json_encode(['success' => false, 'message' => 'Bạn đã có đội rồi! Mỗi người chỉ có thể tham gia một đội.']);
            exit;
        }
        
        // Find team by code
        $stmt = $conn->prepare("SELECT id FROM teams WHERE team_code = ?");
        $stmt->bind_param("s", $teamCode);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy đội với mã ' . $teamCode . '. Vui lòng kiểm tra lại mã đội.']);
            exit;
        }
        
        $team = $result->fetch_assoc();
        $teamId = (int)$team['id'];
        
        // Check if team is full (max 7 members)
        if (getTeamMemberCount($conn, $teamId) >= 7) {
            echo json_encode(['success' => false, 'message' => 'Đội đã đầy! Đội chỉ có thể có tối đa 7 thành viên.']);
            exit;
        }
        
        try {
            // Add user to team
            $stmt = $conn->prepare("INSERT INTO team_members (team_id, user_id, role) VALUES (?, ?, 'member')");
            $stmt->bind_param("ii", $teamId, $userId);
            $stmt->execute();
            
            // Get team info
            $teamInfo = getUserTeam($conn, $userId);
            
            echo json_encode([
                'success' => true,
                'message' => 'Tham gia đội thành công!',
                'team' => $teamInfo
            ]);
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi tham gia đội: ' . $e->getMessage()]);
        }
        break;
        
    case 'get_team':
        if (!isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập!']);
            exit;
        }
        
        $userId = getCurrentUserId();
        if ($userId === null) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập!']);
            exit;
        }
        $teamInfo = getUserTeam($conn, $userId);
        
        if ($teamInfo) {
            echo json_encode([
                'success' => true,
                'team' => $teamInfo
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Bạn chưa có đội.'
            ]);
        }
        break;
        
    case 'leave':
        if (!isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập!']);
            exit;
        }
        
        $userId = getCurrentUserId();
        if ($userId === null) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập!']);
            exit;
        }
        
        // Check if user is team leader
        $stmt = $conn->prepare("
            SELECT t.id, tm.role
            FROM teams t
            JOIN team_members tm ON t.id = tm.team_id
            WHERE tm.user_id = ? AND tm.role = 'leader'
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // User is team leader - delete entire team
            $team = $result->fetch_assoc();
            $teamId = (int)$team['id'];
            
            try {
                $conn->begin_transaction();
                
                // Delete team members first (due to foreign key constraints)
                $stmt = $conn->prepare("DELETE FROM team_members WHERE team_id = ?");
                $stmt->bind_param("i", $teamId);
                $stmt->execute();
                
                // Delete team
                $stmt = $conn->prepare("DELETE FROM teams WHERE id = ?");
                $stmt->bind_param("i", $teamId);
                $stmt->execute();
                
                $conn->commit();
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Đã giải tán đội thành công!'
                ]);
                
            } catch (Exception $e) {
                $conn->rollback();
                echo json_encode(['success' => false, 'message' => 'Lỗi giải tán đội: ' . $e->getMessage()]);
            }
        } else {
            // User is regular member - just remove from team
            try {
                $stmt = $conn->prepare("DELETE FROM team_members WHERE user_id = ?");
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                
                if ($stmt->affected_rows > 0) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Đã rời khỏi đội thành công!'
                    ]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Bạn không thuộc đội nào.']);
                }
                
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Lỗi rời đội: ' . $e->getMessage()]);
            }
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Hành động không hợp lệ.']);
        break;
}

$conn->close();
?>
