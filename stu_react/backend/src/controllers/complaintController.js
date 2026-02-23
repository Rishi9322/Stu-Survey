import pool from '../config/database.js';

// @desc    Submit a complaint or suggestion
// @route   POST /api/complaints
// @access  Private
export const submitComplaint = async (req, res) => {
  try {
    const { type, subject, content } = req.body;
    const userRole = req.user.role;

    if (!type || !subject || !content) {
      return res.status(400).json({ 
        message: 'Please provide type, subject and content' 
      });
    }

    if (!['complaint', 'suggestion'].includes(type)) {
      return res.status(400).json({ 
        message: 'Type must be either complaint or suggestion' 
      });
    }

    const [result] = await pool.query(
      `INSERT INTO suggestions_complaints (submitted_by_role, type, subject, description)
       VALUES (?, ?, ?, ?)`,
      [userRole, type, subject, content]
    );

    res.status(201).json({ 
      message: `${type.charAt(0).toUpperCase() + type.slice(1)} submitted successfully`,
      id: result.insertId
    });

  } catch (error) {
    console.error('Submit complaint error:', error);
    res.status(500).json({ message: 'Server error' });
  }
};

// @desc    Get user's complaints and suggestions
// @route   GET /api/complaints/my-complaints
// @access  Private
export const getUserComplaints = async (req, res) => {
  try {
    const userRole = req.user.role;
    const { type, status } = req.query;

    let query = `
      SELECT 
        sc.id,
        sc.type,
        sc.subject,
        sc.description as content,
        sc.status,
        sc.resolution_notes as admin_response,
        sc.resolved_at,
        sc.created_at
      FROM suggestions_complaints sc
      WHERE sc.submitted_by_role = ?
    `;
    const params = [userRole];

    if (type) {
      query += ' AND sc.type = ?';
      params.push(type);
    }

    if (status) {
      query += ' AND sc.status = ?';
      params.push(status);
    }

    query += ' ORDER BY sc.created_at DESC';

    const [complaints] = await pool.query(query, params);

    res.json({ complaints, count: complaints.length });

  } catch (error) {
    console.error('Get user complaints error:', error);
    res.status(500).json({ message: 'Server error' });
  }
};

// @desc    Get all complaints and suggestions (admin)
// @route   GET /api/complaints
// @access  Private/Admin
export const getAllComplaints = async (req, res) => {
  try {
    const { type, status } = req.query;

    let query = `
      SELECT 
        sc.id,
        sc.type,
        sc.subject,
        sc.description as content,
        sc.status,
        sc.submitted_by_role as role,
        sc.resolution_notes as admin_response,
        sc.resolved_at,
        sc.created_at
      FROM suggestions_complaints sc
      WHERE 1=1
    `;
    const params = [];

    if (type) {
      query += ' AND sc.type = ?';
      params.push(type);
    }

    if (status) {
      query += ' AND sc.status = ?';
      params.push(status);
    }

    query += ' ORDER BY sc.created_at DESC';

    const [complaints] = await pool.query(query, params);

    res.json({ complaints, count: complaints.length });

  } catch (error) {
    console.error('Get all complaints error:', error);
    res.status(500).json({ message: 'Server error' });
  }
};

// @desc    Update complaint status and add admin response
// @route   PUT /api/complaints/:id
// @access  Private/Admin
export const updateComplaintStatus = async (req, res) => {
  try {
    const complaintId = req.params.id;
    const { status, admin_response } = req.body;
    const adminId = req.user.id;

    if (!status) {
      return res.status(400).json({ message: 'Please provide status' });
    }

    if (!['pending', 'in_progress', 'resolved'].includes(status)) {
      return res.status(400).json({ 
        message: 'Status must be pending, in_progress, or resolved' 
      });
    }

    const [result] = await pool.query(
      `UPDATE suggestions_complaints 
       SET status = ?, resolution_notes = ?, resolved_by = ?, resolved_at = NOW()
       WHERE id = ?`,
      [status, admin_response || null, status === 'resolved' ? adminId : null, complaintId]
    );

    if (result.affectedRows === 0) {
      return res.status(404).json({ message: 'Complaint not found' });
    }

    res.json({ message: 'Complaint updated successfully' });

  } catch (error) {
    console.error('Update complaint status error:', error);
    res.status(500).json({ message: 'Server error' });
  }
};

// @desc    Delete complaint
// @route   DELETE /api/complaints/:id
// @access  Private/Admin
export const deleteComplaint = async (req, res) => {
  try {
    const complaintId = req.params.id;

    const [result] = await pool.query(
      'DELETE FROM suggestions_complaints WHERE id = ?',
      [complaintId]
    );

    if (result.affectedRows === 0) {
      return res.status(404).json({ message: 'Complaint not found' });
    }

    res.json({ message: 'Complaint deleted successfully' });

  } catch (error) {
    console.error('Delete complaint error:', error);
    res.status(500).json({ message: 'Server error' });
  }
};
