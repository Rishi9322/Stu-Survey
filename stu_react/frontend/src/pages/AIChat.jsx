import { useState, useEffect, useRef } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';
import { aiService } from '../services';
import { toast } from 'react-hot-toast';
import { ArrowLeft, Send, Bot, User as UserIcon, Sparkles } from 'lucide-react';

const AIChat = () => {
  const { user } = useAuth();
  const navigate = useNavigate();
  const [loading, setLoading] = useState(true);
  const [messages, setMessages] = useState([]);
  const [input, setInput] = useState('');
  const [sending, setSending] = useState(false);
  const [insights, setInsights] = useState(null);
  const messagesEndRef = useRef(null);

  useEffect(() => {
    fetchInitialData();
  }, []);

  useEffect(() => {
    scrollToBottom();
  }, [messages]);

  const scrollToBottom = () => {
    messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
  };

  const fetchInitialData = async () => {
    try {
      const [historyData, insightsData] = await Promise.all([
        aiService.getChatHistory(),
        aiService.getInsights().catch(() => ({ insights: null }))
      ]);
      
      setMessages(historyData.messages || []);
      // Handle insights - can be string or object
      const insightContent = insightsData?.insights;
      if (typeof insightContent === 'string') {
        setInsights(insightContent);
      } else if (insightContent?.message) {
        setInsights(insightContent.message);
      } else if (insightContent?.title) {
        setInsights(insightContent.title);
      } else {
        setInsights(null);
      }
    } catch (error) {
      console.error('Error fetching AI data:', error);
      // Don't show error toast for initial load, just set defaults
      setMessages([]);
      setInsights(null);
    } finally {
      setLoading(false);
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!input.trim() || sending) return;

    const userMessage = {
      id: Date.now(),
      role: 'user',
      content: input.trim(),
      timestamp: new Date().toISOString()
    };

    setMessages(prev => [...prev, userMessage]);
    setInput('');
    setSending(true);

    try {
      const response = await aiService.chat({ message: input.trim() });
      
      const aiMessage = {
        id: Date.now() + 1,
        role: 'assistant',
        content: response.response || 'I understand. How can I help you further?',
        timestamp: new Date().toISOString()
      };

      setMessages(prev => [...prev, aiMessage]);
    } catch (error) {
      console.error('Error sending message:', error);
      toast.error('Failed to get AI response');
      
      const errorMessage = {
        id: Date.now() + 1,
        role: 'assistant',
        content: 'Sorry, I encountered an error. Please try again.',
        timestamp: new Date().toISOString()
      };
      setMessages(prev => [...prev, errorMessage]);
    } finally {
      setSending(false);
    }
  };

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50 flex flex-col">
      {/* Header */}
      <header className="bg-white shadow-sm flex-shrink-0">
        <div className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
          <button
            onClick={() => navigate('/dashboard')}
            className="flex items-center gap-2 text-gray-600 hover:text-gray-900 mb-2"
          >
            <ArrowLeft className="w-5 h-5" />
            Back to Dashboard
          </button>
          <div className="flex items-center gap-3">
            <div className="p-2 bg-blue-100 rounded-full">
              <Bot className="w-6 h-6 text-blue-600" />
            </div>
            <div>
              <h1 className="text-2xl font-bold text-gray-900">AI Assistant</h1>
              <p className="text-sm text-gray-600">Get insights and answers about survey data</p>
            </div>
          </div>
        </div>
      </header>

      <div className="flex-1 flex overflow-hidden">
        {/* Chat Area */}
        <div className="flex-1 flex flex-col max-w-4xl mx-auto w-full">
          {/* Messages */}
          <div className="flex-1 overflow-y-auto px-4 py-6 space-y-6">
            {messages.length === 0 ? (
              <div className="text-center py-12">
                <Bot className="w-16 h-16 text-gray-400 mx-auto mb-4" />
                <h3 className="text-lg font-medium text-gray-900 mb-2">Start a conversation</h3>
                <p className="text-gray-600 mb-6">
                  Ask me anything about the survey data, analytics, or get insights!
                </p>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-2xl mx-auto">
                  <button
                    onClick={() => setInput('What are the overall survey statistics?')}
                    className="p-4 bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow text-left"
                  >
                    <p className="text-sm font-medium text-gray-900">Survey Statistics</p>
                    <p className="text-xs text-gray-600 mt-1">Get overall survey stats</p>
                  </button>
                  <button
                    onClick={() => setInput('Show me rating trends')}
                    className="p-4 bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow text-left"
                  >
                    <p className="text-sm font-medium text-gray-900">Rating Trends</p>
                    <p className="text-xs text-gray-600 mt-1">Analyze rating patterns</p>
                  </button>
                  <button
                    onClick={() => setInput('What are common feedback themes?')}
                    className="p-4 bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow text-left"
                  >
                    <p className="text-sm font-medium text-gray-900">Feedback Themes</p>
                    <p className="text-xs text-gray-600 mt-1">Identify common patterns</p>
                  </button>
                  <button
                    onClick={() => setInput('How can we improve response rates?')}
                    className="p-4 bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow text-left"
                  >
                    <p className="text-sm font-medium text-gray-900">Improvements</p>
                    <p className="text-xs text-gray-600 mt-1">Get recommendations</p>
                  </button>
                </div>
              </div>
            ) : (
              <>
                {messages.map((message) => (
                  <div
                    key={message.id}
                    className={`flex gap-4 ${
                      message.role === 'user' ? 'justify-end' : 'justify-start'
                    }`}
                  >
                    {message.role === 'assistant' && (
                      <div className="flex-shrink-0">
                        <div className="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                          <Bot className="w-6 h-6 text-blue-600" />
                        </div>
                      </div>
                    )}
                    
                    <div
                      className={`max-w-xl rounded-lg px-4 py-3 ${
                        message.role === 'user'
                          ? 'bg-blue-600 text-white'
                          : 'bg-white text-gray-900 shadow-sm'
                      }`}
                    >
                      <p className="whitespace-pre-wrap">
                        {typeof message.content === 'string' 
                          ? message.content 
                          : message.content?.message || message.content?.text || String(message.content)}
                      </p>
                      <p
                        className={`text-xs mt-2 ${
                          message.role === 'user' ? 'text-blue-100' : 'text-gray-500'
                        }`}
                      >
                        {message.timestamp ? new Date(message.timestamp).toLocaleTimeString() : ''}
                      </p>
                    </div>

                    {message.role === 'user' && (
                      <div className="flex-shrink-0">
                        <div className="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                          <UserIcon className="w-6 h-6 text-gray-600" />
                        </div>
                      </div>
                    )}
                  </div>
                ))}
                <div ref={messagesEndRef} />
              </>
            )}
          </div>

          {/* Input Form */}
          <div className="border-t bg-white px-4 py-4">
            <form onSubmit={handleSubmit} className="flex gap-4">
              <input
                type="text"
                value={input}
                onChange={(e) => setInput(e.target.value)}
                placeholder="Ask me anything..."
                className="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                disabled={sending}
              />
              <button
                type="submit"
                disabled={!input.trim() || sending}
                className="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
              >
                {sending ? (
                  <>
                    <div className="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div>
                    Sending...
                  </>
                ) : (
                  <>
                    <Send className="w-5 h-5" />
                    Send
                  </>
                )}
              </button>
            </form>
          </div>
        </div>

        {/* Insights Sidebar */}
        {insights && (
          <div className="hidden lg:block w-80 border-l bg-white p-6 overflow-y-auto">
            <div className="flex items-center gap-2 mb-4">
              <Sparkles className="w-5 h-5 text-purple-600" />
              <h2 className="text-lg font-semibold text-gray-900">AI Insights</h2>
            </div>
            <div className="space-y-4">
              <div className="p-4 bg-purple-50 rounded-lg">
                <p className="text-sm text-purple-900">
                  {typeof insights === 'string' ? insights : JSON.stringify(insights)}
                </p>
              </div>
              
              <div className="pt-4 border-t">
                <h3 className="text-sm font-medium text-gray-900 mb-3">Quick Tips</h3>
                <ul className="space-y-2 text-sm text-gray-600">
                  <li className="flex items-start gap-2">
                    <span className="text-blue-600">•</span>
                    Ask about specific departments or courses
                  </li>
                  <li className="flex items-start gap-2">
                    <span className="text-blue-600">•</span>
                    Request comparisons between time periods
                  </li>
                  <li className="flex items-start gap-2">
                    <span className="text-blue-600">•</span>
                    Get recommendations for improvements
                  </li>
                  <li className="flex items-start gap-2">
                    <span className="text-blue-600">•</span>
                    Analyze rating distributions
                  </li>
                </ul>
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  );
};

export default AIChat;
