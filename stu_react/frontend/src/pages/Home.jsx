import { useState, useEffect, useRef } from 'react';
import { Link } from 'react-router-dom';
import { GraduationCap, BarChart3, Users, Shield, ArrowRight, Star, Sparkles, TrendingUp, Award, CheckCircle, Zap, MessageSquare } from 'lucide-react';

const Home = () => {
  const [scrollY, setScrollY] = useState(0);
  const [mousePosition, setMousePosition] = useState({ x: 0, y: 0 });
  const [activeFeature, setActiveFeature] = useState(0);
  const [isVisible, setIsVisible] = useState({});

  useEffect(() => {
    const handleScroll = () => setScrollY(window.scrollY);
    const handleMouseMove = (e) => {
      setMousePosition({ x: e.clientX, y: e.clientY });
    };

    window.addEventListener('scroll', handleScroll);
    window.addEventListener('mousemove', handleMouseMove);

    // Auto-rotate features
    const interval = setInterval(() => {
      setActiveFeature(prev => (prev + 1) % 3);
    }, 4000);

    return () => {
      window.removeEventListener('scroll', handleScroll);
      window.removeEventListener('mousemove', handleMouseMove);
      clearInterval(interval);
    };
  }, []);

  // Parallax calculations
  const parallaxOffset = scrollY * 0.5;
  const mouseParallaxX = (mousePosition.x - (typeof window !== 'undefined' ? window.innerWidth / 2 : 400)) * 0.02;
  const mouseParallaxY = (mousePosition.y - (typeof window !== 'undefined' ? window.innerHeight / 2 : 400)) * 0.02;

  return (
    <div className="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50 overflow-hidden">
      {/* Animated Background Orbs */}
      <div className="fixed inset-0 pointer-events-none overflow-hidden z-0">
        <div 
          className="absolute w-96 h-96 bg-gradient-to-r from-blue-400/30 to-purple-400/30 rounded-full blur-3xl transition-transform duration-300"
          style={{
            top: '10%',
            left: '10%',
            transform: `translate(${mouseParallaxX}px, ${mouseParallaxY}px)`
          }}
        />
        <div 
          className="absolute w-96 h-96 bg-gradient-to-r from-pink-400/30 to-orange-400/30 rounded-full blur-3xl transition-transform duration-300"
          style={{
            bottom: '10%',
            right: '10%',
            transform: `translate(${-mouseParallaxX}px, ${-mouseParallaxY}px)`
          }}
        />
        <div 
          className="absolute w-64 h-64 bg-gradient-to-r from-cyan-400/20 to-blue-400/20 rounded-full blur-3xl transition-transform duration-300"
          style={{
            top: '50%',
            right: '30%',
            transform: `translate(${mouseParallaxX * 1.5}px, ${mouseParallaxY * 1.5}px)`
          }}
        />
      </div>

      {/* Hero Section */}
      <div className="relative z-10">
        {/* Navigation */}
        <nav className="relative z-50 container mx-auto px-6 py-6">
          <div className="flex items-center justify-between backdrop-blur-md bg-white/80 rounded-2xl px-6 py-4 shadow-lg border border-white/50">
            <div className="flex items-center gap-3 group cursor-pointer">
              <div className="p-2 bg-gradient-to-br from-blue-600 via-purple-600 to-pink-600 rounded-xl shadow-lg group-hover:shadow-xl transition-all group-hover:scale-110">
                <GraduationCap className="w-8 h-8 text-white" />
              </div>
              <span className="text-2xl font-bold bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 bg-clip-text text-transparent">
                SurveyPulse
              </span>
            </div>
            <div className="flex gap-4">
              <Link
                to="/login"
                className="px-6 py-2 text-gray-700 hover:text-blue-600 font-semibold transition-all hover:scale-105"
              >
                Login
              </Link>
              <Link
                to="/register"
                className="px-8 py-3 bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 text-white rounded-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all font-semibold relative overflow-hidden group inline-block text-center"
              >
                <span className="relative z-10">Get Started</span>
                <div className="absolute inset-0 bg-gradient-to-r from-pink-600 via-purple-600 to-blue-600 opacity-0 group-hover:opacity-100 transition-opacity"></div>
              </Link>
            </div>
          </div>
        </nav>

        {/* Hero Content with 3D Interactive Element */}
        <div className="relative z-10 container mx-auto px-6 py-20">
          <div className="max-w-6xl mx-auto">
            <div className="flex flex-col lg:flex-row items-center gap-12">
              {/* Left Content */}
              <div className="flex-1 text-center lg:text-left">
                <div className="inline-flex items-center gap-2 px-5 py-2 bg-gradient-to-r from-blue-100 to-purple-100 text-blue-700 rounded-full mb-8 border border-blue-200 shadow-md animate-bounce">
                  <Sparkles className="w-4 h-4" />
                  <span className="text-sm font-bold">Trusted by 500+ Institutions</span>
                </div>
                
                <h1 className="text-5xl md:text-7xl font-extrabold mb-6 leading-tight">
                  <span className="bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 bg-clip-text text-transparent">
                    Transform
                  </span>
                  <br />
                  <span className="text-gray-900">Student Feedback</span>
                  <br />
                  <span className="bg-gradient-to-r from-pink-600 via-orange-500 to-yellow-500 bg-clip-text text-transparent">
                    Into Action
                  </span>
                </h1>
                
                <p className="text-xl text-gray-600 mb-10 max-w-xl leading-relaxed">
                  Experience the future of student satisfaction surveys with <span className="font-bold text-purple-600">AI-powered insights</span>, real-time analytics, and <span className="font-bold text-blue-600">stunning visualizations</span> that drive meaningful change.
                </p>
                
                <div className="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                  <button
                    onClick={() => handleNavClick('/register')}
                    className="group px-10 py-5 bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 text-white rounded-2xl font-bold text-lg hover:shadow-2xl transform hover:-translate-y-2 transition-all flex items-center justify-center gap-3 relative overflow-hidden"
                  >
                    <span className="relative z-10 flex items-center gap-3">
                      Start Free Trial
                      <ArrowRight className="w-6 h-6 group-hover:translate-x-2 transition-transform" />
                    </span>
                    <div className="absolute inset-0 bg-gradient-to-r from-pink-600 via-purple-600 to-blue-600 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                  </button>
                  <button
                    onClick={() => handleNavClick('/demo')}
                    className="group px-10 py-5 bg-white text-gray-800 rounded-2xl font-bold text-lg hover:shadow-xl transform hover:-translate-y-2 transition-all border-2 border-gray-300 hover:border-purple-400 flex items-center justify-center gap-2"
                  >
                    Watch Demo
                    <Zap className="w-5 h-5 text-yellow-500 group-hover:rotate-12 transition-transform" />
                  </button>
                </div>

                {/* Trust Indicators */}
                <div className="flex items-center justify-center lg:justify-start gap-8 mt-10 text-sm text-gray-600 flex-wrap">
                  <div className="flex items-center gap-2">
                    <CheckCircle className="w-5 h-5 text-green-500" />
                    <span>No credit card</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <CheckCircle className="w-5 h-5 text-green-500" />
                    <span>14-day trial</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <CheckCircle className="w-5 h-5 text-green-500" />
                    <span>Cancel anytime</span>
                  </div>
                </div>
              </div>

              {/* Right - 3D Interactive Floating Element */}
              <div className="flex-1 relative">
                <div 
                  className="relative w-full h-[500px] perspective-1000"
                  style={{
                    transform: `translateY(${-scrollY * 0.3}px)`,
                    transition: 'transform 0.1s ease-out'
                  }}
                >
                  {/* Main 3D Card */}
                  <div 
                    className="absolute inset-0 bg-gradient-to-br from-blue-500 via-purple-500 to-pink-500 rounded-3xl shadow-2xl p-8 hover:scale-105 transition-all duration-500"
                    style={{
                      transform: `rotateX(${mouseParallaxY * 0.5}deg) rotateY(${mouseParallaxX * 0.5}deg) translateZ(50px)`,
                      boxShadow: '0 50px 100px rgba(0,0,0,0.2)',
                      transformStyle: 'preserve-3d'
                    }}
                  >
                    <div className="bg-white/95 backdrop-blur-xl rounded-2xl p-6 h-full flex flex-col justify-between">
                      <div>
                        <div className="flex items-center justify-between mb-6">
                          <div className="flex items-center gap-3">
                            <div className="w-12 h-12 bg-gradient-to-br from-blue-600 to-purple-600 rounded-full flex items-center justify-center shadow-lg">
                              <BarChart3 className="w-6 h-6 text-white" />
                            </div>
                            <div>
                              <div className="font-bold text-gray-900">Live Dashboard</div>
                              <div className="text-sm text-gray-500">Updated 2 min ago</div>
                            </div>
                          </div>
                          <div className="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold animate-pulse">
                            ● LIVE
                          </div>
                        </div>

                        {/* Animated Stats */}
                        <div className="space-y-4">
                          <div>
                            <div className="flex justify-between text-sm mb-2">
                              <span className="text-gray-600">Overall Satisfaction</span>
                              <span className="font-bold text-purple-600">94%</span>
                            </div>
                            <div className="h-3 bg-gray-200 rounded-full overflow-hidden">
                              <div 
                                className="h-full bg-gradient-to-r from-blue-500 to-purple-500 rounded-full transition-all duration-1000"
                                style={{ width: '94%' }}
                              />
                            </div>
                          </div>

                          <div>
                            <div className="flex justify-between text-sm mb-2">
                              <span className="text-gray-600">Response Rate</span>
                              <span className="font-bold text-blue-600">87%</span>
                            </div>
                            <div className="h-3 bg-gray-200 rounded-full overflow-hidden">
                              <div 
                                className="h-full bg-gradient-to-r from-cyan-500 to-blue-500 rounded-full transition-all duration-1000"
                                style={{ width: '87%' }}
                              />
                            </div>
                          </div>

                          <div>
                            <div className="flex justify-between text-sm mb-2">
                              <span className="text-gray-600">Engagement Score</span>
                              <span className="font-bold text-pink-600">92%</span>
                            </div>
                            <div className="h-3 bg-gray-200 rounded-full overflow-hidden">
                              <div 
                                className="h-full bg-gradient-to-r from-pink-500 to-orange-500 rounded-full transition-all duration-1000"
                                style={{ width: '92%' }}
                              />
                            </div>
                          </div>
                        </div>
                      </div>

                      {/* Bottom Stats Grid */}
                      <div className="grid grid-cols-3 gap-4 mt-6 pt-6 border-t border-gray-200">
                        <div className="text-center">
                          <div className="text-2xl font-bold text-blue-600">2.4K</div>
                          <div className="text-xs text-gray-500">Responses</div>
                        </div>
                        <div className="text-center">
                          <div className="text-2xl font-bold text-purple-600">156</div>
                          <div className="text-xs text-gray-500">Active Now</div>
                        </div>
                        <div className="text-center">
                          <div className="text-2xl font-bold text-pink-600">4.8★</div>
                          <div className="text-xs text-gray-500">Avg Rating</div>
                        </div>
                      </div>
                    </div>
                  </div>

                  {/* Floating Elements */}
                  <div 
                    className="absolute -top-8 -right-8 w-20 h-20 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-2xl shadow-lg flex items-center justify-center animate-bounce"
                  >
                    <Star className="w-10 h-10 text-white" />
                  </div>

                  <div 
                    className="absolute -bottom-4 -left-4 w-16 h-16 bg-gradient-to-br from-green-400 to-emerald-500 rounded-2xl shadow-lg flex items-center justify-center"
                    style={{ 
                      animation: 'bounce 3s ease-in-out infinite'
                    }}
                  >
                    <Award className="w-8 h-8 text-white" />
                  </div>

                  <div 
                    className="absolute top-1/2 -left-12 w-12 h-12 bg-gradient-to-br from-cyan-400 to-blue-500 rounded-full shadow-lg flex items-center justify-center"
                    style={{ 
                      animation: 'bounce 2s ease-in-out infinite'
                    }}
                  >
                    <TrendingUp className="w-6 h-6 text-white" />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Animated Features Section */}
      <div className="container mx-auto px-6 py-32 relative z-10">
        <div className="text-center mb-20">
          <div className="inline-flex items-center gap-2 px-5 py-2 bg-gradient-to-r from-purple-100 to-pink-100 text-purple-700 rounded-full mb-6 border border-purple-200">
            <Sparkles className="w-4 h-4" />
            <span className="text-sm font-bold">POWERFUL FEATURES</span>
          </div>
          <h2 className="text-5xl md:text-6xl font-extrabold mb-6">
            <span className="bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 bg-clip-text text-transparent">
              Everything You Need
            </span>
          </h2>
          <p className="text-gray-600 text-xl max-w-2xl mx-auto">
            Powerful tools designed to transform how you collect and analyze student feedback
          </p>
        </div>

        <div className="grid md:grid-cols-3 gap-8 mb-16">
          {[
            {
              icon: BarChart3,
              gradient: 'from-blue-500 to-cyan-500',
              title: 'AI-Powered Analytics',
              description: 'Get instant insights with machine learning algorithms that identify trends, patterns, and actionable recommendations automatically.',
              bgGradient: 'from-blue-50 to-cyan-50'
            },
            {
              icon: Users,
              gradient: 'from-purple-500 to-pink-500',
              title: 'Smart Role Management',
              description: 'Intuitive dashboards tailored for students, teachers, and administrators with personalized insights and automated workflows.',
              bgGradient: 'from-purple-50 to-pink-50'
            },
            {
              icon: Shield,
              gradient: 'from-pink-500 to-orange-500',
              title: 'Bank-Grade Security',
              description: 'Enterprise encryption, JWT authentication, GDPR compliance, and role-based access control keep your data safe 24/7.',
              bgGradient: 'from-pink-50 to-orange-50'
            }
          ].map((feature, index) => {
            const Icon = feature.icon;
            const isActive = activeFeature === index;
            
            return (
              <div
                key={index}
                onMouseEnter={() => setActiveFeature(index)}
                className={`group p-8 bg-gradient-to-br ${feature.bgGradient} rounded-3xl shadow-lg hover:shadow-2xl transition-all transform hover:-translate-y-4 border-2 cursor-pointer ${
                  isActive ? 'border-purple-400 scale-105' : 'border-transparent'
                }`}
              >
                <div className={`p-5 bg-gradient-to-br ${feature.gradient} rounded-2xl w-fit mb-6 group-hover:scale-110 group-hover:rotate-6 transition-all shadow-xl`}>
                  <Icon className="w-10 h-10 text-white" />
                </div>
                <h3 className="text-2xl font-bold mb-4 text-gray-900 group-hover:text-transparent group-hover:bg-gradient-to-r group-hover:from-blue-600 group-hover:to-purple-600 group-hover:bg-clip-text transition-all">
                  {feature.title}
                </h3>
                <p className="text-gray-600 leading-relaxed text-lg">
                  {feature.description}
                </p>
                <div className={`mt-6 flex items-center gap-2 text-purple-600 font-semibold opacity-0 group-hover:opacity-100 transition-all`}>
                  Learn more <ArrowRight className="w-4 h-4 group-hover:translate-x-2 transition-transform" />
                </div>
              </div>
            );
          })}
        </div>
      </div>

      {/* Scrolling Stats Section */}
      <div 
        className="relative py-32 overflow-hidden"
        style={{
          transform: `translateY(${scrollY * 0.1}px)`
        }}
      >
        <div className="absolute inset-0 bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600"></div>
        <div className="absolute inset-0 bg-black/10"></div>
        
        <div className="relative z-10 container mx-auto px-6">
          <div className="grid md:grid-cols-4 gap-8 text-center text-white">
            {[
              { number: '500+', label: 'Active Institutions', icon: Users },
              { number: '10K+', label: 'Surveys Completed', icon: MessageSquare },
              { number: '95%', label: 'Satisfaction Rate', icon: Star },
              { number: '24/7', label: 'System Uptime', icon: Shield }
            ].map((stat, index) => {
              const Icon = stat.icon;
              return (
                <div 
                  key={index}
                  className="group hover:scale-110 transition-all cursor-pointer"
                >
                  <div className="inline-flex items-center justify-center w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl mb-4 group-hover:bg-white/30 transition-all group-hover:rotate-12">
                    <Icon className="w-8 h-8" />
                  </div>
                  <div className="text-6xl font-extrabold mb-3 group-hover:scale-110 transition-transform">
                    {stat.number}
                  </div>
                  <div className="text-xl text-white/90 font-medium">{stat.label}</div>
                </div>
              );
            })}
          </div>
        </div>
      </div>

      {/* Final CTA Section */}
      <div className="container mx-auto px-6 py-32 relative z-10">
        <div className="relative bg-gradient-to-br from-blue-600 via-purple-600 to-pink-600 rounded-[3rem] p-16 text-center text-white overflow-hidden">
          {/* Animated background elements */}
          <div className="absolute inset-0 opacity-20">
            <div className="absolute top-0 left-0 w-64 h-64 bg-white rounded-full blur-3xl animate-pulse"></div>
            <div className="absolute bottom-0 right-0 w-96 h-96 bg-white rounded-full blur-3xl animate-pulse" style={{ animationDelay: '1s' }}></div>
          </div>

          <div className="relative z-10">
            <div className="inline-flex items-center gap-2 px-5 py-2 bg-white/20 backdrop-blur-sm rounded-full mb-8">
              <Zap className="w-5 h-5 text-yellow-300" />
              <span className="text-sm font-bold">LIMITED TIME OFFER</span>
            </div>
            
            <h2 className="text-5xl md:text-6xl font-extrabold mb-6">
              Ready to Transform Your Surveys?
            </h2>
            <p className="text-2xl text-white/90 mb-12 max-w-3xl mx-auto font-medium">
              Join 500+ institutions already using SurveyPulse to improve student satisfaction and drive meaningful change.
            </p>
            
            <div className="flex flex-col sm:flex-row gap-6 justify-center items-center">
              <button
                onClick={() => handleNavClick('/register')}
                className="group px-12 py-6 bg-white text-purple-600 rounded-2xl font-bold text-xl hover:shadow-2xl transform hover:-translate-y-2 transition-all flex items-center gap-3"
              >
                Start Free 14-Day Trial
                <ArrowRight className="w-6 h-6 group-hover:translate-x-2 transition-transform" />
              </button>
              <div className="text-white/90 text-sm">
                <div className="flex items-center gap-2 mb-1">
                  <CheckCircle className="w-5 h-5 text-green-300" />
                  <span>No credit card required</span>
                </div>
                <div className="flex items-center gap-2">
                  <CheckCircle className="w-5 h-5 text-green-300" />
                  <span>Setup in under 5 minutes</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Footer */}
      <footer className="border-t border-gray-200 py-12 bg-gradient-to-br from-gray-50 to-white relative z-10">
        <div className="container mx-auto px-6">
          <div className="flex flex-col md:flex-row justify-between items-center gap-6">
            <div className="flex items-center gap-3">
              <div className="p-2 bg-gradient-to-br from-blue-600 to-purple-600 rounded-xl">
                <GraduationCap className="w-6 h-6 text-white" />
              </div>
              <span className="text-xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                SurveyPulse
              </span>
            </div>
            <p className="text-gray-600">&copy; 2025 SurveyPulse. All rights reserved.</p>
            <div className="flex gap-4 text-sm text-gray-600">
              <button onClick={() => alert('Privacy page')} className="hover:text-purple-600 transition-colors">Privacy</button>
              <button onClick={() => alert('Terms page')} className="hover:text-purple-600 transition-colors">Terms</button>
              <button onClick={() => alert('Contact page')} className="hover:text-purple-600 transition-colors">Contact</button>
            </div>
          </div>
        </div>
      </footer>
    </div>
  );
};

export default Home;