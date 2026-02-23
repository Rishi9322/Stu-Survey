import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { analyticsService, aiService } from '../services';
import { toast } from 'react-hot-toast';
import { ArrowLeft, Brain, TrendingUp, AlertCircle, CheckCircle, Lightbulb, MessageSquare, Zap, Lock, Sparkles } from 'lucide-react';

const AIInsights = () => {
  const navigate = useNavigate();
  const [loading, setLoading] = useState(true);
  const [insights, setInsights] = useState(null);
  const [stats, setStats] = useState(null);
  const [models, setModels] = useState([]);
  const [selectedModel, setSelectedModel] = useState('gpt-3.5-turbo');
  const [chatMessage, setChatMessage] = useState('');
  const [chatHistory, setChatHistory] = useState([]);
  const [isChatting, setIsChatting] = useState(false);

  useEffect(() => {
    fetchInsights();
    fetchModels();
  }, []);

  const fetchInsights = async () => {
    try {
      const [insightsData, statsData] = await Promise.all([
        analyticsService.getInsights().catch(() => null),
        analyticsService.getStatistics()
      ]);
      
      setInsights(insightsData);
      setStats(statsData.statistics);
    } catch (error) {
      console.error('Error fetching insights:', error);
      toast.error('Failed to load AI insights');
    } finally {
      setLoading(false);
    }
  };

  const fetchModels = async () => {
    try {
      const data = await aiService.getModels();
      setModels(data.models || []);
    } catch (error) {
      console.error('Error fetching models:', error);
    }
  };

  const handleChat = async (e) => {
    e.preventDefault();
    if (!chatMessage.trim() || isChatting) return;

    const userMessage = chatMessage.trim();
    setChatMessage('');
    setChatHistory(prev => [...prev, { role: 'user', content: userMessage }]);
    setIsChatting(true);

    try {
      const response = await aiService.chat({ message: userMessage, model: selectedModel });
      setChatHistory(prev => [...prev, { 
        role: 'assistant', 
        content: response.response || response.message || 'No response received' 
      }]);
    } catch (error) {
      console.error('Chat error:', error);
      setChatHistory(prev => [...prev, { 
        role: 'assistant', 
        content: 'Sorry, I encountered an error. Please try again.' 
      }]);
    } finally {
      setIsChatting(false);
    }
  };

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gray-50">
        <div className="text-center">
          <div className="animate-spin rounded-full h-16 w-16 border-b-4 border-blue-600 mx-auto mb-4"></div>
          <p className="text-gray-600">Loading AI Insights...</p>
        </div>
      </div>
    );
  }

  const surveyCompletionRate = stats?.surveyCompletion?.students 
    ? Math.round((stats.surveyCompletion.students.completed / stats.surveyCompletion.students.total) * 100) 
    : 0;

  const teacherCompletionRate = stats?.surveyCompletion?.teachers 
    ? Math.round((stats.surveyCompletion.teachers.completed / stats.surveyCompletion.teachers.total) * 100) 
    : 0;

  return (
    <div className="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50">
      {/* Header */}
      <header className="bg-white/80 backdrop-blur-sm shadow-sm sticky top-0 z-10">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
          <button
            onClick={() => navigate('/dashboard')}
            className="flex items-center gap-2 text-gray-600 hover:text-gray-900 mb-4 transition-colors"
          >
            <ArrowLeft className="w-5 h-5" />
            Back to Dashboard
          </button>
          <div className="flex items-center gap-4">
            <div className="p-3 bg-gradient-to-br from-blue-600 to-purple-600 rounded-2xl shadow-lg">
              <Brain className="w-8 h-8 text-white" />
            </div>
            <div>
              <h1 className="text-3xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                AI-Powered Insights
              </h1>
              <p className="text-gray-600 mt-1">Data-driven recommendations and analysis</p>
            </div>
          </div>
        </div>
      </header>

      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        {/* Key Metrics Cards */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div className="bg-white rounded-2xl shadow-lg p-6 border-l-4 border-blue-500">
            <div className="flex items-center justify-between mb-4">
              <h3 className="text-gray-600 font-semibold">Student Participation</h3>
              <TrendingUp className="w-6 h-6 text-blue-500" />
            </div>
            <div className="text-4xl font-bold text-blue-600 mb-2">{surveyCompletionRate}%</div>
            <p className="text-sm text-gray-500">
              {stats?.surveyCompletion?.students?.completed || 0} of {stats?.surveyCompletion?.students?.total || 0} completed
            </p>
          </div>

          <div className="bg-white rounded-2xl shadow-lg p-6 border-l-4 border-green-500">
            <div className="flex items-center justify-between mb-4">
              <h3 className="text-gray-600 font-semibold">Teacher Participation</h3>
              <CheckCircle className="w-6 h-6 text-green-500" />
            </div>
            <div className="text-4xl font-bold text-green-600 mb-2">{teacherCompletionRate}%</div>
            <p className="text-sm text-gray-500">
              {stats?.surveyCompletion?.teachers?.completed || 0} of {stats?.surveyCompletion?.teachers?.total || 0} completed
            </p>
          </div>

          <div className="bg-white rounded-2xl shadow-lg p-6 border-l-4 border-purple-500">
            <div className="flex items-center justify-between mb-4">
              <h3 className="text-gray-600 font-semibold">Total Responses</h3>
              <Lightbulb className="w-6 h-6 text-purple-500" />
            </div>
            <div className="text-4xl font-bold text-purple-600 mb-2">{stats?.totalResponses || 0}</div>
            <p className="text-sm text-gray-500">Survey responses collected</p>
          </div>
        </div>

        {/* AI Insights Section */}
        <div className="bg-white rounded-2xl shadow-lg p-8">
          <div className="flex items-center gap-3 mb-6">
            <div className="p-2 bg-gradient-to-br from-blue-500 to-purple-500 rounded-lg">
              <Brain className="w-6 h-6 text-white" />
            </div>
            <h2 className="text-2xl font-bold text-gray-900">AI Analysis</h2>
          </div>

          {insights?.insight ? (
            <div className="space-y-4">
              <div className="bg-gradient-to-r from-blue-50 to-purple-50 rounded-xl p-6 border border-blue-100">
                <p className="text-gray-800 leading-relaxed text-lg">{insights.insight}</p>
              </div>
            </div>
          ) : (
            <div className="text-center py-12">
              <Brain className="w-16 h-16 text-gray-300 mx-auto mb-4" />
              <h3 className="text-xl font-semibold text-gray-700 mb-2">No AI Insights Available Yet</h3>
              <p className="text-gray-500 max-w-md mx-auto">
                AI insights will be generated once sufficient survey data has been collected. 
                Encourage more users to complete their surveys to unlock intelligent analysis.
              </p>
            </div>
          )}
        </div>

        {/* Recommendations Section */}
        <div className="bg-white rounded-2xl shadow-lg p-8">
          <div className="flex items-center gap-3 mb-6">
            <Lightbulb className="w-6 h-6 text-yellow-500" />
            <h2 className="text-2xl font-bold text-gray-900">Recommendations</h2>
          </div>

          <div className="space-y-4">
            {surveyCompletionRate < 50 && (
              <div className="flex gap-4 p-4 bg-yellow-50 rounded-xl border border-yellow-200">
                <AlertCircle className="w-6 h-6 text-yellow-600 flex-shrink-0 mt-1" />
                <div>
                  <h4 className="font-semibold text-yellow-900 mb-1">Low Student Participation</h4>
                  <p className="text-yellow-800 text-sm">
                    Consider sending reminder emails or announcements to increase survey completion rates.
                  </p>
                </div>
              </div>
            )}

            {teacherCompletionRate < 50 && (
              <div className="flex gap-4 p-4 bg-yellow-50 rounded-xl border border-yellow-200">
                <AlertCircle className="w-6 h-6 text-yellow-600 flex-shrink-0 mt-1" />
                <div>
                  <h4 className="font-semibold text-yellow-900 mb-1">Low Teacher Participation</h4>
                  <p className="text-yellow-800 text-sm">
                    Reach out to teachers to encourage survey completion for more comprehensive feedback.
                  </p>
                </div>
              </div>
            )}

            {(stats?.pendingItems?.complaints || 0) > 5 && (
              <div className="flex gap-4 p-4 bg-red-50 rounded-xl border border-red-200">
                <AlertCircle className="w-6 h-6 text-red-600 flex-shrink-0 mt-1" />
                <div>
                  <h4 className="font-semibold text-red-900 mb-1">Pending Complaints</h4>
                  <p className="text-red-800 text-sm">
                    {stats.pendingItems.complaints} complaints are awaiting review. Address these promptly to maintain satisfaction.
                  </p>
                </div>
              </div>
            )}

            {surveyCompletionRate >= 80 && teacherCompletionRate >= 80 && (
              <div className="flex gap-4 p-4 bg-green-50 rounded-xl border border-green-200">
                <CheckCircle className="w-6 h-6 text-green-600 flex-shrink-0 mt-1" />
                <div>
                  <h4 className="font-semibold text-green-900 mb-1">Excellent Participation!</h4>
                  <p className="text-green-800 text-sm">
                    Great job! High participation rates ensure comprehensive and reliable feedback data.
                  </p>
                </div>
              </div>
            )}
          </div>
        </div>

        {/* System Status */}
        <div className="bg-gradient-to-br from-blue-600 to-purple-600 rounded-2xl shadow-lg p-8 text-white">
          <h2 className="text-2xl font-bold mb-6">System Overview</h2>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <p className="text-blue-100 mb-2">Total Users</p>
              <p className="text-3xl font-bold">
                {(stats?.users?.student || 0) + (stats?.users?.teacher || 0) + (stats?.users?.admin || 0)}
              </p>
            </div>
            <div>
              <p className="text-blue-100 mb-2">Pending Issues</p>
              <p className="text-3xl font-bold">
                {(stats?.pendingItems?.complaints || 0) + (stats?.pendingItems?.suggestions || 0)}
              </p>
            </div>
          </div>
        </div>

        {/* AI Models Selection */}
        <div className="bg-white rounded-2xl shadow-lg p-8">
          <div className="flex items-center gap-3 mb-6">
            <div className="p-2 bg-gradient-to-br from-emerald-500 to-teal-500 rounded-lg">
              <Sparkles className="w-6 h-6 text-white" />
            </div>
            <h2 className="text-2xl font-bold text-gray-900">Available AI Models</h2>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
            {models.map((model) => (
              <div
                key={model.id}
                onClick={() => model.available && setSelectedModel(model.id)}
                className={`relative p-5 rounded-xl border-2 transition-all duration-300 cursor-pointer ${
                  selectedModel === model.id
                    ? 'border-blue-500 bg-blue-50 shadow-lg shadow-blue-200/50'
                    : model.available
                    ? 'border-gray-200 hover:border-blue-300 hover:shadow-md bg-white'
                    : 'border-gray-100 bg-gray-50 cursor-not-allowed opacity-60'
                }`}
              >
                {selectedModel === model.id && (
                  <div className="absolute -top-2 -right-2 w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center">
                    <CheckCircle className="w-4 h-4 text-white" />
                  </div>
                )}
                <div className="flex items-start justify-between mb-3">
                  <div className="p-2 bg-gradient-to-br from-blue-500 to-purple-500 rounded-lg">
                    <Zap className="w-5 h-5 text-white" />
                  </div>
                  {!model.available && (
                    <Lock className="w-5 h-5 text-gray-400" />
                  )}
                </div>
                <h3 className="font-bold text-gray-900 mb-1">{model.name}</h3>
                <p className="text-sm text-gray-600">{model.description}</p>
                {model.available ? (
                  <span className="inline-flex items-center mt-3 text-xs font-medium text-green-700 bg-green-100 px-2.5 py-0.5 rounded-full">
                    <span className="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5 animate-pulse"></span>
                    Available
                  </span>
                ) : (
                  <span className="inline-flex items-center mt-3 text-xs font-medium text-gray-500 bg-gray-100 px-2.5 py-0.5 rounded-full">
                    Coming Soon
                  </span>
                )}
              </div>
            ))}
          </div>

          {/* Chat Interface */}
          <div className="border-t border-gray-200 pt-6">
            <div className="flex items-center gap-3 mb-4">
              <MessageSquare className="w-5 h-5 text-blue-600" />
              <h3 className="text-lg font-semibold text-gray-900">Chat with {models.find(m => m.id === selectedModel)?.name || 'AI'}</h3>
            </div>

            {/* Chat Messages */}
            <div className="bg-gray-50 rounded-xl p-4 h-80 overflow-y-auto mb-4 space-y-4">
              {chatHistory.length === 0 ? (
                <div className="h-full flex flex-col items-center justify-center text-gray-400">
                  <MessageSquare className="w-12 h-12 mb-3 opacity-50" />
                  <p className="text-center">Start a conversation with AI</p>
                  <p className="text-sm text-center mt-1">Ask questions about survey data, get insights, or request analysis</p>
                </div>
              ) : (
                chatHistory.map((msg, index) => (
                  <div
                    key={index}
                    className={`flex ${msg.role === 'user' ? 'justify-end' : 'justify-start'}`}
                  >
                    <div
                      className={`max-w-[80%] p-4 rounded-2xl ${
                        msg.role === 'user'
                          ? 'bg-gradient-to-br from-blue-600 to-purple-600 text-white rounded-br-md'
                          : 'bg-white border border-gray-200 text-gray-800 rounded-bl-md shadow-sm'
                      }`}
                    >
                      {msg.role === 'assistant' && (
                        <div className="flex items-center gap-2 mb-2 pb-2 border-b border-gray-100">
                          <Brain className="w-4 h-4 text-blue-600" />
                          <span className="text-xs font-medium text-blue-600">AI Assistant</span>
                        </div>
                      )}
                      <p className="text-sm whitespace-pre-wrap">{msg.content}</p>
                    </div>
                  </div>
                ))
              )}
              {isChatting && (
                <div className="flex justify-start">
                  <div className="bg-white border border-gray-200 text-gray-800 p-4 rounded-2xl rounded-bl-md shadow-sm">
                    <div className="flex items-center gap-2">
                      <div className="flex space-x-1">
                        <div className="w-2 h-2 bg-blue-500 rounded-full animate-bounce" style={{ animationDelay: '0ms' }}></div>
                        <div className="w-2 h-2 bg-blue-500 rounded-full animate-bounce" style={{ animationDelay: '150ms' }}></div>
                        <div className="w-2 h-2 bg-blue-500 rounded-full animate-bounce" style={{ animationDelay: '300ms' }}></div>
                      </div>
                      <span className="text-sm text-gray-500">AI is thinking...</span>
                    </div>
                  </div>
                </div>
              )}
            </div>

            {/* Chat Input */}
            <form onSubmit={handleChat} className="flex gap-3">
              <input
                type="text"
                value={chatMessage}
                onChange={(e) => setChatMessage(e.target.value)}
                placeholder="Ask anything about survey insights..."
                className="flex-1 px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                disabled={isChatting}
              />
              <button
                type="submit"
                disabled={isChatting || !chatMessage.trim()}
                className="px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-medium rounded-xl hover:from-blue-700 hover:to-purple-700 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
              >
                <MessageSquare className="w-5 h-5" />
                Send
              </button>
            </form>
          </div>
        </div>
      </main>
    </div>
  );
};

export default AIInsights;
