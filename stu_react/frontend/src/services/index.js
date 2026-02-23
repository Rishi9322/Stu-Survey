import api from './api';

// Auth Services
export const authService = {
  register: async (userData) => {
    const response = await api.post('/auth/register', userData);
    return response.data;
  },

  login: async (credentials) => {
    const response = await api.post('/auth/login', credentials);
    if (response.data.token) {
      localStorage.setItem('token', response.data.token);
      localStorage.setItem('user', JSON.stringify(response.data.user));
    }
    return response.data;
  },

  logout: () => {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
  },

  getCurrentUser: async () => {
    const response = await api.get('/auth/me');
    return response.data;
  },

  updateProfile: async (profileData) => {
    const response = await api.put('/auth/profile', profileData);
    return response.data;
  },

  changePassword: async (passwordData) => {
    const response = await api.put('/auth/change-password', passwordData);
    return response.data;
  }
};

// Survey Services
export const surveyService = {
  getQuestions: async () => {
    const response = await api.get('/surveys/questions');
    return response.data;
  },

  submitResponses: async (responsesData) => {
    const response = await api.post('/surveys/responses', responsesData);
    return response.data;
  },

  getUserResponses: async () => {
    const response = await api.get('/surveys/my-responses');
    return response.data;
  },

  getCompletionStatus: async () => {
    const response = await api.get('/surveys/completion-status');
    return response.data;
  },

  getTeachers: async () => {
    const response = await api.get('/surveys/teachers');
    return response.data;
  },

  submitTeacherRating: async (ratingData) => {
    const response = await api.post('/surveys/teacher-rating', ratingData);
    return response.data;
  }
};

// User Management Services (Admin)
export const userService = {
  getAll: async (filters = {}) => {
    const response = await api.get('/users', { params: filters });
    return response.data;
  },

  getAllUsers: async (filters = {}) => {
    const response = await api.get('/users', { params: filters });
    return response.data;
  },

  getUserById: async (userId) => {
    const response = await api.get(`/users/${userId}`);
    return response.data;
  },

  create: async (userData) => {
    const response = await api.post('/users', userData);
    return response.data;
  },

  update: async (userId, userData) => {
    const response = await api.put(`/users/${userId}`, userData);
    return response.data;
  },

  updateUserStatus: async (userId, isActive) => {
    const response = await api.put(`/users/${userId}/status`, { is_active: isActive });
    return response.data;
  },

  resetUserPassword: async (userId) => {
    const response = await api.put(`/users/${userId}/reset-password`);
    return response.data;
  },

  delete: async (userId) => {
    const response = await api.delete(`/users/${userId}`);
    return response.data;
  },

  deleteUser: async (userId) => {
    const response = await api.delete(`/users/${userId}`);
    return response.data;
  }
};

// Analytics Services
export const analyticsService = {
  getStatistics: async () => {
    const response = await api.get('/analytics/statistics');
    return response.data;
  },

  getRatingDistribution: async () => {
    const response = await api.get('/analytics/rating-distribution');
    return response.data;
  },

  getDepartmentAnalytics: async () => {
    const response = await api.get('/analytics/department-analytics');
    return response.data;
  },

  getRecentActivity: async (limit = 10) => {
    const response = await api.get('/analytics/recent-activity', { params: { limit } });
    return response.data;
  },

  getInsights: async () => {
    const response = await api.get('/ai/insights');
    return response.data;
  }
};

// Complaint Services
export const complaintService = {
  submitComplaint: async (complaintData) => {
    const response = await api.post('/complaints', complaintData);
    return response.data;
  },

  getUserComplaints: async (filters = {}) => {
    const response = await api.get('/complaints/my-complaints', { params: filters });
    return response.data;
  },

  getAllComplaints: async (filters = {}) => {
    const response = await api.get('/complaints', { params: filters });
    return response.data;
  },

  updateComplaintStatus: async (complaintId, statusData) => {
    const response = await api.put(`/complaints/${complaintId}`, statusData);
    return response.data;
  },

  deleteComplaint: async (complaintId) => {
    const response = await api.delete(`/complaints/${complaintId}`);
    return response.data;
  }
};

// AI Services
export const aiService = {
  chat: async (messageData) => {
    const response = await api.post('/ai/chat', messageData);
    return response.data;
  },

  getChatHistory: async () => {
    // Return empty history since backend doesn't persist chat
    return { messages: [] };
  },

  getInsights: async () => {
    const response = await api.get('/ai/insights');
    return response.data;
  },

  getModels: async () => {
    // Return available AI models
    return {
      models: [
        { id: 'gpt-4', name: 'GPT-4', description: 'Most capable model for complex tasks', available: true },
        { id: 'gpt-3.5-turbo', name: 'GPT-3.5 Turbo', description: 'Fast and efficient for most tasks', available: true },
        { id: 'claude-3', name: 'Claude 3', description: 'Anthropic\'s most advanced model', available: false },
        { id: 'gemini-pro', name: 'Gemini Pro', description: 'Google\'s multimodal AI', available: false },
        { id: 'llama-2', name: 'Llama 2', description: 'Meta\'s open source model', available: false }
      ]
    };
  },

  getTrainingData: async () => {
    const response = await api.get('/ai/training');
    return response.data;
  },

  addTrainingData: async (trainingData) => {
    const response = await api.post('/ai/training', trainingData);
    return response.data;
  }
};
