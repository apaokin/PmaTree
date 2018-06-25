require 'csv'
class LineInfo
  attr_reader :name, :without_page, :parent, :line
  def initialize(name, parent, line, without_page = false)
    @line = line
    @name = name
    @without_page = without_page
    @parent = parent
  end
end

class LineInfoCombo
  attr_reader :ru_name, :eng_name, :without_page, :parent, :line
  def initialize(ru_info, eng_info)
    if ru_info.without_page != eng_info.without_page || ru_info.line != eng_info.line || ru_info.parent != eng_info.parent
      raise "ERROR"
    end
    @line = ru_info.line
    @ru_name = ru_info.name
    @eng_name = eng_info.name
    @without_page = ru_info.without_page
    @parent = ru_info.parent
  end
end


class Parser
  attr_reader :infos
  def read_file(file)
    @chars = []
    @infos = []
    File.open(file, "r") do |infile|
      while (line = infile.gets)
        @line = line.gsub("\n",'')
        next if @line.empty? || @line[/\[/]
        proccess_line
      end
    end
  end

  def first_control_chars
    raise "error in #{@line}" unless @line[/^#+|^=+|{{/]
    @chars.push @line[/^#+|^=+|^{{/]
  end

  def last_chars
    @chars.last
  end

  def last_char
    @chars.last[0]
  end

  def proccess_pound
    if @line[/level/]
      name = name_pound_level
      @infos.push(LineInfo.new(name, find_less_pound, @infos.count))
    else
      name = name_pound_without_level
      @infos.push(LineInfo.new(name, find_less_pound, @infos.count, 'without_page_and_header'))
    end
  end

  def proccess_brackets
    proccess_pound
  end

  def name_pound_level
    @line[/{{.*}}/].split('|').last.gsub(/}}/, '').gsub(/^\s+/, '').gsub(/\s+$/, '')

  end

  def name_pound_without_level
    @line.gsub(/^#*|#*$/, '').gsub(/^\s+/, '').gsub(/\s+$/, '')
  end


  def name_eq
    @line.gsub(/^=*|=*$/, '').gsub(/^\s+/, '').gsub(/\s+$/, '')
  end

  def find_less_pound
    @chars.rindex { |c| c.length < last_chars.length && c[0] == '#' || c[0] == '=' }
  end

  def find_less_eq
    return nil if last_chars == '='
    @chars.rindex { |c| c.length < last_chars.length && c[0] == '=' }
  end

  def proccess_eq
    @infos.push(LineInfo.new(name_eq, find_less_eq, @infos.count, 'without_page'))
  end

  def proccess_line
    first_control_chars
    case last_char
    when '#'
      proccess_pound
    when '='
      proccess_eq
    when '{'
      proccess_brackets
    else
      raise "ERROR"
    end
  end

  def to_s
    @infos.map{ |i| i.inspect }
  end
end

@parser = Parser.new
@parser.read_file('wiki_class.txt')
@eng_parser = Parser.new
@eng_parser.read_file('wiki_eng_class.txt')
infos = @parser.infos.each_with_index.map { |v, i| LineInfoCombo.new(v,@eng_parser.infos[i])}

CSV.open('PmaTreePma.csv', 'wb') do |csv|
  csv << %w[line ru_name eng_name without_page parent]
  infos.each do |i|
    csv << [i.line, i.ru_name, i.eng_name, i.without_page, i.parent]
  end
  # csv << %w[line ru_name eng_name without_page parent]
  # csv << ["row", "of", "CSV", "data"]
  # csv << ["another", "row"]
end
